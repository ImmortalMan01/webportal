<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}
require 'db.php';
$currentUser = $_SESSION['user'];
$partner = $_POST['user'] ?? '';
$message = trim($_POST['message'] ?? '');
if ($message === '') {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$currentUser]);
$currentId = $stmt->fetchColumn();
$stmt->execute([$partner]);
$partnerId = $stmt->fetchColumn();
if (!$currentId || !$partnerId) {
    http_response_code(404);
    exit;
}

$s = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
$s->execute([$currentId, $partnerId, $message]);
$id = $pdo->lastInsertId();

$inserted = $pdo->prepare('SELECT id, sender_id, message, created_at FROM messages WHERE id = ?');
$inserted->execute([$id]);
$row = $inserted->fetch();

header('Content-Type: application/json');
echo json_encode($row);
