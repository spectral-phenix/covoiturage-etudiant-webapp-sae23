<?php
// API réservée à l'admin
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Vérif session admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit();
}

require_once 'db.php';

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// Tables autorisées (whitelist sécurité)
$allowed_tables = ['etudiant','vehicule','lieu','trajet','horaire','membre_equipage','domicile'];

function validTable($t, $allowed) {
    if (!in_array($t, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Table non autorisée']);
        exit();
    }
}

$table = $_GET['table'] ?? $body['table'] ?? null;
if (!$table) { http_response_code(400); echo json_encode(['error' => 'Table manquante']); exit(); }
validTable($table, $allowed_tables);

//  GET : liste tous les enregistrements 
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id");
    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
    exit();
}

//  POST : insertion 
if ($method === 'POST') {
    $data = $body;
    unset($data['table']);

    if (empty($data)) { http_response_code(400); echo json_encode(['error' => 'Corps vide']); exit(); }

    $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));

    $stmt = $pdo->prepare("INSERT INTO `$table` ($cols) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit();
}

//  PUT : mise à jour 
if ($method === 'PUT') {
    $data = $body;
    unset($data['table']);
    $id = $data['id'] ?? null;
    unset($data['id']);

    if (!$id || empty($data)) { http_response_code(400); echo json_encode(['error' => 'Données manquantes']); exit(); }

    $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
    $stmt = $pdo->prepare("UPDATE `$table` SET $set WHERE id = ?");
    $stmt->execute([...array_values($data), $id]);
    echo json_encode(['success' => true]);
    exit();
}

//  DELETE 
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID manquant']); exit(); }

    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit();
}
