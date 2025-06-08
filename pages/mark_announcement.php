<?php
session_start();
if(!isset($_SESSION['user'])){
    http_response_code(403);
    exit;
}
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
update_activity($pdo);
$pdo->exec("CREATE TABLE IF NOT EXISTS announcement_views (user_id INT PRIMARY KEY, last_seen DATETIME NOT NULL, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$_SESSION['user']]);
$userId = $stmt->fetchColumn();
if(!$userId){
    http_response_code(404);
    exit;
}
$upd = $pdo->prepare('INSERT INTO announcement_views (user_id,last_seen) VALUES (?,NOW()) ON DUPLICATE KEY UPDATE last_seen=NOW()');
$upd->execute([$userId]);
header('Content-Type: application/json');
echo json_encode(['status'=>'ok']);
