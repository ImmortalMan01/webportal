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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-4">
        <h2 class="mb-3">Admin Panel</h2>
        <h3>Kullanıcılar</h3>
        <ul class="list-group">
            <?php foreach ($users as $info): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($info['username']) . ' (' . $info['role'] . ')'; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
