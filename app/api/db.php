<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Paramètres de connexion : lus dans config.php (non versionné) ou dans les
// variables d'environnement (Docker). Voir config.example.php.
$CONFIG = file_exists(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];

function conf($key, $default = null) {
    global $CONFIG;
    $env = getenv($key);
    if ($env !== false) return $env;
    return $CONFIG[$key] ?? $default;
}

function getDB() {
    $host   = conf('DB_HOST', 'localhost');
    $dbname = conf('DB_NAME', 'FCSM');
    $user   = conf('DB_USER', 'root');
    $pass   = conf('DB_PASS', '');

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Connexion BDD échouée : ' . $e->getMessage()]);
        exit();
    }
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function getBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function requireLogin() {
    if (empty($_SESSION['id_etudiant'])) {
        jsonResponse(['error' => 'Non connecté'], 401);
    }
    return (int) $_SESSION['id_etudiant'];
}
