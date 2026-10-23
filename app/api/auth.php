<?php
require_once 'db.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'me') {
    // Session admin
    if (!empty($_SESSION['is_admin'])) {
        jsonResponse(['connected' => true, 'is_admin' => true]);
    }

    if (empty($_SESSION['id_etudiant'])) {
        jsonResponse(['connected' => false]);
    }

    $stmt = $pdo->prepare('SELECT id, nom, prenom, email, groupe FROM etudiant WHERE id = ?');
    $stmt->execute([$_SESSION['id_etudiant']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        jsonResponse(['connected' => false]);
    }

    jsonResponse(['connected' => true, 'is_admin' => false, 'user' => $user]);
}

if ($method === 'POST') {
    $body = getBody();
    $action = $body['action'] ?? '';

    if ($action === 'login') {
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        //  Compte admin spécial 
        if ($email === conf('ADMIN_LOGIN', 'admin') && $password === conf('ADMIN_PASSWORD', 'root')) {
            $_SESSION['is_admin'] = true;
            unset($_SESSION['id_etudiant']);
            jsonResponse(['success' => true, 'is_admin' => true]);
        }

        $stmt = $pdo->prepare('SELECT id, nom, prenom, email, groupe, password FROM etudiant WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || $user['password'] !== $password) {
            jsonResponse(['error' => 'Email ou mot de passe incorrect'], 401);
        }

        $_SESSION['id_etudiant'] = (int) $user['id'];
        unset($_SESSION['is_admin']);
        unset($user['password']);
        jsonResponse(['success' => true, 'is_admin' => false, 'user' => $user]);
    }

    if ($action === 'logout') {
        session_destroy();
        jsonResponse(['success' => true]);
    }
}

jsonResponse(['error' => 'Action inconnue'], 400);
