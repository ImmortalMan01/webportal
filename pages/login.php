<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/settings.php';
$registrations_open = get_setting($pdo, 'registrations_open', '1');
$hide_register_button = get_setting($pdo, 'hide_register_button', '0');
$error = '';
$success = false;
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
        $success = true;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/login.css">
</head>
<body>
    <div class="ring">
        <i style="--clr:#00ff0a;"></i>
        <i style="--clr:#ff0057;"></i>
        <i style="--clr:#fffd44;"></i>
        <div class="login">
            <h2>Giriş Yap</h2>
            <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="post">
                <div class="inputBx">
                    <input type="text" name="username" placeholder="Kullanıcı Adı" required>
                </div>
                <div class="inputBx">
                    <input type="password" name="password" placeholder="Şifre" required>
                    <i class="toggle-password bi bi-eye"></i>
                </div>
                <div class="inputBx">
                    <input type="submit" value="Giriş Yap">
                </div>
                <div class="links">
                    <a href="#">Şifremi Unuttum</a>
                    <?php if ($registrations_open == '1' && $hide_register_button != '1'): ?>
                    <a href="register.php">Kayıt Ol</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <?php if ($success): ?>
    <div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3">
        <div id="loginToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Giriş başarılı, yönlendiriliyor.</div>
            </div>
        </div>
    </div>
    <script>
        const toastEl = document.getElementById('loginToast');
        const toast = new bootstrap.Toast(toastEl, {delay: 3000});
        toast.show();
        setTimeout(()=>{ window.location = '../index.php'; }, 3000);
    </script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.toggle-password').forEach(function(el) {
            el.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                }
            });
        });
    </script>
</body>
</html>
