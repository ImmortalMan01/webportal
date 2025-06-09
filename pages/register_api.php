<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/settings.php';
require __DIR__ . '/../includes/roles.php';

header('Content-Type: application/json');

$registrations_open = get_setting($pdo, 'registrations_open', '1');
if ($registrations_open != '1') {
    echo json_encode(['success' => false, 'error' => 'Kayıtlar şu anda kapalı']);
    exit;
}

$u = $_POST['username'] ?? '';
$e = $_POST['email'] ?? '';
$p = $_POST['password'] ?? '';
$r = $_POST['role'] ?? 'Normal Personel';

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
$stmt->execute([$u, $e]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'error' => 'Kullanıcı adı veya e-posta zaten mevcut']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$u, $e, password_hash($p, PASSWORD_DEFAULT), $r]);

echo json_encode(['success' => true]);
