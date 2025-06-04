<?php
session_start();
require __DIR__ . '/../includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT username, password, role FROM users WHERE username = ?');
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $up = $pdo->prepare('UPDATE users SET last_active=NOW() WHERE username=?');
        $up->execute([$user['username']]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 login-card">
        <h2 class="text-center mb-3">Giriş Yap</h2>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post">
            <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Kullanıcı Adı" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Şifre" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
