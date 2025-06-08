<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}
require __DIR__ . '/../includes/db.php';
$current = $_SESSION['user'];
$target = $_POST['user'] ?? '';
if ($target === '') {
    http_response_code(400);
    exit;
}
$stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
$stmt->execute([$current]);
$currentId = $stmt->fetchColumn();
$stmt->execute([$target]);
$targetId = $stmt->fetchColumn();
if (!$currentId || !$targetId) {
    http_response_code(404);
    exit;
}
$check = $pdo->prepare('SELECT 1 FROM user_mutes WHERE user_id=? AND muted_user_id=?');
$check->execute([$currentId,$targetId]);
if ($check->fetch()) {
    $pdo->prepare('DELETE FROM user_mutes WHERE user_id=? AND muted_user_id=?')->execute([$currentId,$targetId]);
    $muted = false;
} else {
    $pdo->prepare('INSERT INTO user_mutes (user_id, muted_user_id) VALUES (?,?)')->execute([$currentId,$targetId]);
    $muted = true;
}
header('Content-Type: application/json');
echo json_encode(['muted'=>$muted]);

