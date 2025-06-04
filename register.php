<?php
session_start();
require 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$u]);
    if ($stmt->fetchColumn() > 0) {
        $message = 'Kullanıcı adı zaten mevcut';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$u, password_hash($p, PASSWORD_DEFAULT), 'user']);
        $message = 'Kayıt başarılı. Giriş yapabilirsiniz.';
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Kayıt Ol</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Kayıt Ol</h2>
        <?php if ($message) echo "<p>$message</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Kayıt Ol</button>
        </form>
    </div>
</body>
</html>
