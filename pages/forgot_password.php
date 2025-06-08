<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/password_reset.php';
require __DIR__ . '/../includes/mailer.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    if ($identifier !== '') {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();
        if ($user) {
            $token = create_reset_token($pdo, $user['id']);
            $link = sprintf('http://%s%s?token=%s', $_SERVER['HTTP_HOST'], dirname($_SERVER['PHP_SELF']) . '/reset_password.php', $token);
            send_mail($pdo, $user['email'], 'Password Reset', "Şifre sıfırlama bağlantınız: $link");
        }
        $message = 'Eğer hesap mevcutsa, e-posta ile bir bağlantı gönderildi.';
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
        <h2>Şifre Sıfırlama</h2>
        <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>
        <form method="post">
            <div class="inputBx">
                <label for="identifier" class="form-label">Kullanıcı Adı veya E-posta</label>
                <input id="identifier" type="text" name="identifier" placeholder="Kullanıcı Adı veya E-posta" required>
            </div>
            <div class="inputBx">
                <input type="submit" value="Gönder">
            </div>
            <div class="links">
                <a href="login.php">Giriş Yap</a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/theme.js"></script>
</body>
</html>
