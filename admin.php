<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}
$users = json_decode(file_get_contents('users.json'), true);
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
            <?php foreach ($users as $name => $info): ?>
                <li><?php echo htmlspecialchars($name) . ' (' . $info['role'] . ')'; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
