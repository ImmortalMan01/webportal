<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents('users.json'), true);
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if (isset($users[$u]) && password_verify($p, $users[$u]['password'])) {
        $_SESSION['user'] = $u;
        $_SESSION['role'] = $users[$u]['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Hatalı kullanıcı adı veya şifre';
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Giriş Yap</h2>
        <?php if ($error) echo "<p>$error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Giriş Yap</button>
        </form>
    </div>
</body>
</html>
