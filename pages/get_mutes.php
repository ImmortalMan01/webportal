<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}
require __DIR__ . '/../includes/db.php';
$current = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
$stmt->execute([$current]);
$uid = $stmt->fetchColumn();
$list = [];
if ($uid) {
    $q = $pdo->prepare('SELECT u.username FROM user_mutes um JOIN users u ON um.muted_user_id=u.id WHERE um.user_id=?');
    $q->execute([$uid]);
    $list = array_column($q->fetchAll(), 'username');
}
header('Content-Type: application/json');
echo json_encode($list);

