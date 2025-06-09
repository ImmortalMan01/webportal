<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';

header('Content-Type: application/json');

$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';
if ($u === '' || $p === '') {
    echo json_encode(['success' => false, 'error' => 'Kullanıcı adı ve şifre gerekli']);
    exit;
}
$stmt = $pdo->prepare('SELECT username, password, role FROM users WHERE username = ?');
$stmt->execute([$u]);
$user = $stmt->fetch();
if ($user && password_verify($p, $user['password'])) {
    $_SESSION['user'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $up = $pdo->prepare('UPDATE users SET last_active=NOW() WHERE username=?');
    $up->execute([$user['username']]);
    log_activity($pdo, 'login');
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Hatalı kullanıcı adı veya şifre']);
}
