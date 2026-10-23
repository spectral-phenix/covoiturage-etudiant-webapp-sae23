<?php
require_once 'db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id_etudiant = requireLogin();

if ($method === 'GET') {
    $type = $_GET['type'] ?? 'all'; 

    // Liste des membres d'un trajet 
    if ($type === 'membres') {
        $id_trajet = (int)($_GET['id_trajet'] ?? 0);
        if (!$id_trajet) jsonResponse(['error' => 'ID trajet manquant'], 400);

        $chk = $pdo->prepare("SELECT id FROM trajet WHERE id = ? AND id_createur = ?");
        $chk->execute([$id_trajet, $id_etudiant]);
        if (!$chk->fetch()) jsonResponse(['error' => 'Non autorisé'], 403);

        $stmt = $pdo->prepare("
            SELECT me.id, me.role, me.id_etudiant,
                   e.nom, e.prenom, e.groupe
            FROM membre_equipage me
            JOIN etudiant e ON e.id = me.id_etudiant
            WHERE me.id_trajet = ?
            ORDER BY me.role DESC, e.nom
        ");
        $stmt->execute([$id_trajet]);
        jsonResponse($stmt->fetchAll());
    }

    if ($type === 'mes') {
        // Trajets où l'étudiant est membre
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   ld.nom AS lieu_depart_nom, ld.ville AS lieu_depart_ville,
                   la.nom AS lieu_arrivee_nom, la.ville AS lieu_arrivee_ville,
                   v.marque, v.modele, v.immatriculation, v.nb_places,
                   me.role,
                   e.nom AS createur_nom, e.prenom AS createur_prenom,
                   (SELECT COUNT(*) FROM membre_equipage WHERE id_trajet = t.id) AS nb_membres
            FROM trajet t
            JOIN membre_equipage me ON me.id_trajet = t.id AND me.id_etudiant = ?
            JOIN lieu ld ON ld.id = t.id_lieu_depart
            JOIN lieu la ON la.id = t.id_lieu_arrivee
            LEFT JOIN vehicule v ON v.id = t.id_vehicule
            JOIN etudiant e ON e.id = t.id_createur
            ORDER BY t.date_trajet, t.heure_depart
        ");
        $stmt->execute([$id_etudiant]);
    } elseif ($type === 'disponibles') {
        // Trajets non pleins où l'étudiant n'est pas déjà inscrit
        $stmt = $pdo->prepare("
            SELECT t.*,
                   ld.nom AS lieu_depart_nom, ld.ville AS lieu_depart_ville,
                   la.nom AS lieu_arrivee_nom, la.ville AS lieu_arrivee_ville,
                   v.marque, v.modele, v.nb_places,
                   e.nom AS createur_nom, e.prenom AS createur_prenom,
                   (SELECT COUNT(*) FROM membre_equipage WHERE id_trajet = t.id) AS nb_membres
            FROM trajet t
            JOIN lieu ld ON ld.id = t.id_lieu_depart
            JOIN lieu la ON la.id = t.id_lieu_arrivee
            LEFT JOIN vehicule v ON v.id = t.id_vehicule
            JOIN etudiant e ON e.id = t.id_createur
            WHERE t.estPlein = 0
              AND t.id NOT IN (
                  SELECT id_trajet FROM membre_equipage WHERE id_etudiant = ?
              )
            ORDER BY t.date_trajet, t.heure_depart
        ");
        $stmt->execute([$id_etudiant]);
    }

    jsonResponse($stmt->fetchAll());
}

if ($method === 'POST') {
    $body = getBody();
    $action = $body['action'] ?? 'create';

    if ($action === 'create') {
        // Créer un trajet
        $stmt = $pdo->prepare("
            INSERT INTO trajet (date_trajet, heure_depart, heure_arrivee_estimee, type, id_createur, id_lieu_depart, id_lieu_arrivee, id_vehicule, participation, remarques)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $body['date_trajet'],
            $body['heure_depart'],
            $body['heure_arrivee_estimee'] ?? null,
            $body['type'] ?? 'aller',
            $id_etudiant,
            $body['id_lieu_depart'],
            $body['id_lieu_arrivee'],
            $body['id_vehicule'] ?? null,
            $body['participation'] ?? null,
            $body['remarques'] ?? null
        ]);
        $id_trajet = $pdo->lastInsertId();

        // Ajouter le créateur comme conducteur
        $stmt2 = $pdo->prepare("INSERT INTO membre_equipage (id_trajet, id_etudiant, role) VALUES (?, ?, 'conducteur')");
        $stmt2->execute([$id_trajet, $id_etudiant]);

        jsonResponse(['success' => true, 'id' => $id_trajet]);
    }

    if ($action === 'rejoindre') {
        $id_trajet = $body['id_trajet'];

        // Vérifie que le trajet existe et n'est pas plein
        $stmt = $pdo->prepare("
            SELECT t.id_vehicule, v.nb_places,
                   (SELECT COUNT(*) FROM membre_equipage WHERE id_trajet = t.id) AS nb_membres
            FROM trajet t
            LEFT JOIN vehicule v ON v.id = t.id_vehicule
            WHERE t.id = ? AND t.estPlein = 0
        ");
        $stmt->execute([$id_trajet]);
        $trajet = $stmt->fetch();

        if (!$trajet) {
            jsonResponse(['error' => 'Trajet introuvable ou complet'], 400);
        }

        // Ajoute comme passager
        $stmt2 = $pdo->prepare("INSERT IGNORE INTO membre_equipage (id_trajet, id_etudiant, role) VALUES (?, ?, 'passager')");
        $stmt2->execute([$id_trajet, $id_etudiant]);

        // Vérifie si complet après ajout
        if ($trajet['nb_places'] && ($trajet['nb_membres'] + 1) >= $trajet['nb_places']) {
            $pdo->prepare("UPDATE trajet SET estPlein = 1 WHERE id = ?")->execute([$id_trajet]);
        }

        jsonResponse(['success' => true]);
    }

    //  Expulser un membre (conducteur seulement) 
    if ($action === 'kick') {
        $id_trajet   = (int)($body['id_trajet']   ?? 0);
        $id_cible    = (int)($body['id_etudiant'] ?? 0);

        if (!$id_trajet || !$id_cible) jsonResponse(['error' => 'Données manquantes'], 400);

        // Vérifier que le demandeur est le créateur
        $chk = $pdo->prepare("SELECT id FROM trajet WHERE id = ? AND id_createur = ?");
        $chk->execute([$id_trajet, $id_etudiant]);
        if (!$chk->fetch()) jsonResponse(['error' => 'Non autorisé'], 403);

        // On ne peut pas expulser le conducteur lui-même
        if ($id_cible === $id_etudiant) jsonResponse(['error' => 'Impossible de vous expulser vous-même'], 400);

        $pdo->prepare("DELETE FROM membre_equipage WHERE id_trajet = ? AND id_etudiant = ?")
            ->execute([$id_trajet, $id_cible]);
        // Remettre estPlein à 0 si quelqu'un a été retiré
        $pdo->prepare("UPDATE trajet SET estPlein = 0 WHERE id = ?")->execute([$id_trajet]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'quitter') {
        $id_trajet = $body['id_trajet'];
        $stmt = $pdo->prepare("DELETE FROM membre_equipage WHERE id_trajet = ? AND id_etudiant = ?");
        $stmt->execute([$id_trajet, $id_etudiant]);
        // Remet estPlein à 0
        $pdo->prepare("UPDATE trajet SET estPlein = 0 WHERE id = ?")->execute([$id_trajet]);
        jsonResponse(['success' => true]);
    }
}

if ($method === 'DELETE') {
    $id_trajet = $_GET['id'] ?? null;
    if (!$id_trajet) jsonResponse(['error' => 'ID manquant'], 400);

    // Vérifie que l'étudiant est le créateur
    $stmt = $pdo->prepare("SELECT id FROM trajet WHERE id = ? AND id_createur = ?");
    $stmt->execute([$id_trajet, $id_etudiant]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Non autorisé'], 403);
    }

    // Les tables sont en MyISAM (pas de clé étrangère ON DELETE CASCADE) :
    // on supprime aussi l'équipage pour ne pas laisser de lignes orphelines.
    $pdo->prepare("DELETE FROM membre_equipage WHERE id_trajet = ?")->execute([$id_trajet]);
    $pdo->prepare("DELETE FROM trajet WHERE id = ?")->execute([$id_trajet]);
    jsonResponse(['success' => true]);
}
