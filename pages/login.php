<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/settings.php';
require __DIR__ . '/../includes/activity.php';
$registrations_open = get_setting($pdo, 'registrations_open', '1');
$hide_register_button = get_setting($pdo, 'hide_register_button', '0');
$error = '';
$login_success = false;
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
        log_activity($pdo, 'login');
        $login_success = true;
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
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <input id="username" type="text" name="username" placeholder="Kullanıcı Adı" required>
                </div>
                <div class="inputBx">
                    <label for="password" class="form-label">Şifre</label>
                    <div class="input-group">
                        <input id="password" type="password" name="password" placeholder="Şifre" class="form-control" required>
                        <span class="input-group-text"><i class="toggle-password bi bi-eye"></i></span>
                    </div>
                </div>
                <div class="inputBx">
                    <button type="submit" class="button" id="loginButton">
                        <span class="text">Giriş Yap</span>
                        <div class="progress-bar"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 11">
                            <polyline class="check" points="1.4,5.8 5.1,9.5 11.6,2.1"/>
                        </svg>
                    </button>
                </div>
                <div class="links">
                    <a href="forgot_password.php">Şifremi Unuttum</a>
                    <?php if ($registrations_open == '1' && $hide_register_button != '1'): ?>
                    <a href="register.php">Kayıt Ol</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/theme.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script>
        window.loginSuccess = <?php echo $login_success ? 'true' : 'false'; ?>;
    </script>
    <script>
        document.querySelectorAll('.toggle-password').forEach(function(el) {
            el.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input');
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

        function playSuccessAnimation() {
            var button = document.getElementById('loginButton');
            var text = button.querySelector('.text');
            var progressBar = button.querySelector('.progress-bar');
            var checkPath = button.querySelector('.check');
            var offset = anime.setDashoffset(checkPath);
            checkPath.setAttribute('stroke-dashoffset', offset);
            var startWidth = button.offsetWidth;

            var tl = anime.timeline();
            tl.add({ targets: text, duration: 1, opacity: 0 })
              .add({ targets: button, duration: 1300, height: 10, width: startWidth, backgroundColor: '#2B2D2F', borderRadius: 100 })
              .add({ targets: progressBar, duration: 2000, width: startWidth, easing: 'linear' })
              .add({ targets: button, width: 0, duration: 1 })
              .add({ targets: progressBar, width: 80, height: 80, delay: 500, duration: 750, borderRadius: 80, backgroundColor: '#71DFBE' })
              .add({ targets: checkPath, strokeDashoffset: [offset, 0], duration: 200, easing: 'easeInOutSine' });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (window.loginSuccess) {
                playSuccessAnimation();
                setTimeout(function() {
                    window.location.href = '../index.php';
                }, 3500);
            }
        });
    </script>
</body>
</html>
