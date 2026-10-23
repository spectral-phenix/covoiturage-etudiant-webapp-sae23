<?php
require_once 'db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id_etudiant = requireLogin();

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'profil';

    if ($action === 'profil') {
        $stmt = $pdo->prepare("
            SELECT e.id, e.nom, e.prenom, e.email, e.groupe,
                   l.id AS id_lieu, l.adresse, l.ville, l.code_postal, l.latitude, l.longitude,
                   d.type AS domicile_type
            FROM etudiant e
            LEFT JOIN domicile d ON d.id_etudiant = e.id AND d.type = 'principale'
            LEFT JOIN lieu l ON l.id = d.id_lieu
            WHERE e.id = ?
        ");
        $stmt->execute([$id_etudiant]);
        jsonResponse($stmt->fetch());
    }

    if ($action === 'lieux') {
        // Tous les lieux (usage admin)
        $stmt = $pdo->query("SELECT id, nom, ville, type FROM lieu ORDER BY type, nom");
        jsonResponse($stmt->fetchAll());
    }

    if ($action === 'lieux_trajet') {
        // Lieux pour créer un trajet : tout sauf les domiciles des autres étudiants
        $stmt = $pdo->prepare("
            SELECT id, nom, ville, type FROM lieu
            WHERE type != 'domicile'
               OR id IN (
                   SELECT id_lieu FROM domicile WHERE id_etudiant = ?
               )
            ORDER BY type, nom
        ");
        $stmt->execute([$id_etudiant]);
        jsonResponse($stmt->fetchAll());
    }

    if ($action === 'horaires') {
        $stmt = $pdo->prepare("
            SELECT h.*, l.nom AS lieu_nom FROM horaire h
            JOIN lieu l ON l.id = h.id_lieu
            WHERE h.id_etudiant = ?
            ORDER BY FIELD(h.jour_semaine,'lundi','mardi','mercredi','jeudi','vendredi','samedi'), h.heure_debut
        ");
        $stmt->execute([$id_etudiant]);
        jsonResponse($stmt->fetchAll());
    }
}

if ($method === 'POST') {
    $body = getBody();
    $action = $body['action'] ?? 'update_profil';

    if ($action === 'update_profil') {
        $stmt = $pdo->prepare("UPDATE etudiant SET nom=?, prenom=?, email=?, groupe=? WHERE id=?");
        $stmt->execute([$body['nom'], $body['prenom'], $body['email'], $body['groupe'], $id_etudiant]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'update_mdp') {
        $stmt = $pdo->prepare("UPDATE etudiant SET password=? WHERE id=?");
        $stmt->execute([$body['password'], $id_etudiant]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'update_domicile') {
        // Vérifie si un lieu domicile existe déjà
        $stmt = $pdo->prepare("SELECT d.id_lieu FROM domicile d WHERE d.id_etudiant = ? AND d.type = 'principale'");
        $stmt->execute([$id_etudiant]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt2 = $pdo->prepare("UPDATE lieu SET adresse=?, ville=?, code_postal=?, latitude=?, longitude=? WHERE id=?");
            $stmt2->execute([$body['adresse'], $body['ville'], $body['code_postal'], $body['latitude'] ?? null, $body['longitude'] ?? null, $existing['id_lieu']]);
        } else {
            $stmt2 = $pdo->prepare("INSERT INTO lieu (nom, adresse, ville, type, code_postal, latitude, longitude) VALUES (?, ?, ?, 'domicile', ?, ?, ?)");
            $stmt2->execute(['Domicile de ' . $id_etudiant, $body['adresse'], $body['ville'], $body['code_postal'], $body['latitude'] ?? null, $body['longitude'] ?? null]);
            $id_lieu = $pdo->lastInsertId();
            $stmt3 = $pdo->prepare("INSERT INTO domicile (type, id_etudiant, id_lieu) VALUES ('principale', ?, ?)");
            $stmt3->execute([$id_etudiant, $id_lieu]);
        }
        jsonResponse(['success' => true]);
    }
}
