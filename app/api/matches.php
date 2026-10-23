<?php
require_once 'db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id_etudiant = requireLogin();

if ($method === 'GET') {
    // Récupère les horaires de l'étudiant connecté
    $stmt = $pdo->prepare("SELECT * FROM horaire WHERE id_etudiant = ?");
    $stmt->execute([$id_etudiant]);
    $mes_horaires = $stmt->fetchAll();

    if (empty($mes_horaires)) {
        jsonResponse([]);
    }

    $matches = [];

    foreach ($mes_horaires as $h) {
        // Trouve les étudiants avec même jour ET même heure de début (±15 min), hors soi-même
        $stmt2 = $pdo->prepare("
            SELECT e.id, e.nom, e.prenom, e.groupe,
                   h.jour_semaine, h.heure_debut, h.heure_fin, h.type,
                   l.nom AS lieu_nom, l.latitude, l.longitude,
                   dom_lieu.latitude AS dom_lat, dom_lieu.longitude AS dom_lng,
                   CONCAT(dom_lieu.adresse, ', ', dom_lieu.ville) AS domicile
            FROM horaire h
            JOIN etudiant e ON h.id_etudiant = e.id
            JOIN lieu l ON h.id_lieu = l.id
            LEFT JOIN domicile d ON d.id_etudiant = e.id AND d.type = 'principale'
            LEFT JOIN lieu dom_lieu ON dom_lieu.id = d.id_lieu
            WHERE h.id_etudiant != ?
              AND h.jour_semaine = ?
              AND ABS(TIME_TO_SEC(h.heure_debut) - TIME_TO_SEC(?)) <= 900
        ");
        $stmt2->execute([$id_etudiant, $h['jour_semaine'], $h['heure_debut']]);
        $compat = $stmt2->fetchAll();

        // Récupère la lat/lng du domicile principal de l'étudiant connecté
        $stmt3 = $pdo->prepare("
            SELECT l.latitude, l.longitude FROM domicile d
            JOIN lieu l ON l.id = d.id_lieu
            WHERE d.id_etudiant = ? AND d.type = 'principale'
            LIMIT 1
        ");
        $stmt3->execute([$id_etudiant]);
        $mon_domicile = $stmt3->fetch();

        foreach ($compat as $c) {
            // Calcul distance Haversine (en km)
            $distance = null;
            if ($mon_domicile && $c['dom_lat'] && $c['dom_lng']) {
                $lat1 = deg2rad($mon_domicile['latitude']);
                $lat2 = deg2rad($c['dom_lat']);
                $dlat = deg2rad($c['dom_lat'] - $mon_domicile['latitude']);
                $dlng = deg2rad($c['dom_lng'] - $mon_domicile['longitude']);
                $a = sin($dlat/2)**2 + cos($lat1)*cos($lat2)*sin($dlng/2)**2;
                $distance = round(6371 * 2 * atan2(sqrt($a), sqrt(1-$a)), 1);
            }
            $matches[] = [
                'id' => $c['id'],
                'nom' => $c['nom'],
                'prenom' => $c['prenom'],
                'groupe' => $c['groupe'],
                'jour' => $c['jour_semaine'],
                'heure_debut' => $c['heure_debut'],
                'lieu' => $c['lieu_nom'],
                'domicile' => $c['domicile'],
                'distance_km' => $distance
            ];
        }
    }

    // Déduplique par id étudiant et trie par distance
    $seen = [];
    $unique = [];
    foreach ($matches as $m) {
        if (!isset($seen[$m['id']])) {
            $seen[$m['id']] = true;
            $unique[] = $m;
        }
    }
    usort($unique, fn($a, $b) => ($a['distance_km'] ?? 9999) <=> ($b['distance_km'] ?? 9999));

    jsonResponse($unique);
}
