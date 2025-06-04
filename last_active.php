<?php
require 'db.php';
$user = $_GET['user'] ?? '';
$stmt = $pdo->prepare('SELECT last_active FROM users WHERE username=?');
$stmt->execute([$user]);
$last = $stmt->fetchColumn();
header('Content-Type: application/json');
echo json_encode(['last_active'=>$last]);
?>
