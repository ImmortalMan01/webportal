<?php
session_start();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents('users.json'), true);
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if (isset($users[$u])) {
        $message = 'Kullanıcı adı zaten mevcut';
    } else {
        $users[$u] = ['password' => password_hash($p, PASSWORD_DEFAULT), 'role' => 'user'];
        file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
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
