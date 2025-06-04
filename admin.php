<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}
require 'db.php';
$stmt = $pdo->query('SELECT username, role FROM users ORDER BY username');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Admin Panel</h2>
        <h3>Kullanıcılar</h3>
        <ul>
            <?php foreach ($users as $info): ?>
                <li><?php echo htmlspecialchars($info['username']) . ' (' . $info['role'] . ')'; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
