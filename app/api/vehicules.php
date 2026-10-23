<?php
require_once 'db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id_etudiant = requireLogin();

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM vehicule WHERE id_etudiant = ? ORDER BY id");
    $stmt->execute([$id_etudiant]);
    jsonResponse($stmt->fetchAll());
}

if ($method === 'POST') {
    $body = getBody();
    $stmt = $pdo->prepare("
        INSERT INTO vehicule (immatriculation, nb_places, id_etudiant, marque, modele, date_ct, date_assurance)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $body['immatriculation'],
        $body['nb_places'] ?? 5,
        $id_etudiant,
        $body['marque'] ?? null,
        $body['modele'] ?? null,
        $body['date_ct'] ?? null,
        $body['date_assurance'] ?? null
    ]);
    jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
}

if ($method === 'PUT') {
    $body = getBody();
    $id = $body['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

    $stmt = $pdo->prepare("
        UPDATE vehicule SET immatriculation=?, nb_places=?, marque=?, modele=?, date_ct=?, date_assurance=?
        WHERE id=? AND id_etudiant=?
    ");
    $stmt->execute([
        $body['immatriculation'],
        $body['nb_places'] ?? 5,
        $body['marque'] ?? null,
        $body['modele'] ?? null,
        $body['date_ct'] ?? null,
        $body['date_assurance'] ?? null,
        $id,
        $id_etudiant
    ]);
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

    $stmt = $pdo->prepare("DELETE FROM vehicule WHERE id=? AND id_etudiant=?");
    $stmt->execute([$id, $id_etudiant]);
    jsonResponse(['success' => true]);
}
