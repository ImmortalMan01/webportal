<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/password_reset.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;
$userId = $token ? get_user_id_by_token($pdo, $token) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new1 = $_POST['password'] ?? '';
    $new2 = $_POST['password2'] ?? '';
    if ($userId && $new1 !== '' && $new1 === $new2) {
        update_user_password($pdo, $userId, $new1);
        invalidate_token($pdo, $token);
        $success = true;
    } else {
        $message = 'Geçersiz token veya şifreler uyuşmuyor.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Şifre Sıfırla</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/login.css">
</head>
<body>
<div class="ring">
    <i style="--clr:#00ff0a;"></i>
    <i style="--clr:#ff0057;"></i>
    <i style="--clr:#fffd44;"></i>
    <div class="login">
        <h2>Yeni Şifre</h2>
        <?php if ($success): ?>
            <div class='alert alert-success'>Şifre güncellendi. <a href="login.php">Giriş yap</a>.</div>
        <?php elseif (!$userId): ?>
            <div class='alert alert-danger'>Geçersiz veya süresi dolmuş bağlantı.</div>
        <?php else: ?>
            <?php if ($message) echo "<div class='alert alert-danger'>$message</div>"; ?>
            <form method="post">
                <div class="inputBx">
                    <input type="password" name="password" placeholder="Yeni Şifre" required>
                </div>
                <div class="inputBx">
                    <input type="password" name="password2" placeholder="Yeni Şifre (Tekrar)" required>
                </div>
                <div class="inputBx">
                    <input type="submit" value="Sıfırla">
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/theme.js"></script>
</body>
</html>
