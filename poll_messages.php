<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}
require 'db.php';
$currentUser = $_SESSION['user'];
$partner = $_GET['user'] ?? '';
$lastId = isset($_GET['last']) ? (int)$_GET['last'] : 0;

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$currentUser]);
$currentId = $stmt->fetchColumn();
$stmt->execute([$partner]);
$partnerId = $stmt->fetchColumn();
if (!$currentId || !$partnerId) {
    http_response_code(404);
    exit;
}

$q = $pdo->prepare('SELECT id, sender_id, message, created_at FROM messages WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY id');
$q->execute([$currentId, $partnerId, $partnerId, $currentId, $lastId]);
$rows = $q->fetchAll();

$markIds = [];
foreach ($rows as $r) {
    if ($r['sender_id'] == $partnerId) {
        $markIds[] = $r['id'];
    }
}
if ($markIds) {
    $placeholders = implode(',', array_fill(0, count($markIds), '?'));
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id IN ($placeholders)");
    $stmt->execute($markIds);
}

header('Content-Type: application/json');
echo json_encode($rows);
