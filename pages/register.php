<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/settings.php';
require __DIR__ . '/../includes/roles.php';
$registrations_open = get_setting($pdo, 'registrations_open', '1');
$hide_register_button = get_setting($pdo, 'hide_register_button', '0');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($registrations_open != '1') {
        $message = 'Kayıtlar yetkililer tarafından devre dışı bırakıldı.';
    } else {
        $u = $_POST['username'] ?? '';
        $e = $_POST['email'] ?? '';
        $p = $_POST['password'] ?? '';
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$u, $e]);
        if ($stmt->fetchColumn() > 0) {
            $message = 'Kullanıcı adı veya e-posta zaten mevcut';
        } else {
            $r = $_POST['role'] ?? 'Normal Personel';
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$u, $e, password_hash($p, PASSWORD_DEFAULT), $r]);
            $message = 'Kayıt başarılı. Giriş yapabilirsiniz.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kayıt Ol</title>
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
            <h2>Kayıt Ol</h2>
            <?php if ($registrations_open != '1') echo "<div class='alert alert-warning'>Kayıtlar şu anda kapalı.</div>"; ?>
            <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>
            <div id="register-error" class="alert alert-danger" style="display:none"></div>
            <form onsubmit="return false;">
                <div class="inputBx">
                    <label for="reg_username" class="form-label">Kullanıcı Adı</label>
                    <input id="reg_username" type="text" name="username" placeholder="Kullanıcı Adı" required>
                </div>
                <div class="inputBx">
                    <label for="reg_email" class="form-label">E-posta</label>
                    <input id="reg_email" type="email" name="email" placeholder="E-posta" required>
                </div>
                <div class="inputBx">
                    <label for="reg_password" class="form-label">Şifre</label>
                    <div class="input-group">
                        <input id="reg_password" type="password" name="password" placeholder="Şifre" class="form-control" required>
                        <span class="input-group-text"><i class="toggle-password bi bi-eye"></i></span>
                    </div>
                </div>
                <div class="inputBx">
                    <label for="reg_role" class="form-label">Rol</label>
                    <select id="reg_role" name="role">
                        <?php foreach (default_roles() as $r): ?>
                        <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="inputBx">
                    <button id="register-button" class="ready" type="button">
                        <div class="message submitMessage">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 12.2"><polyline stroke="currentColor" points="2,7.1 6.5,11.1 11,7.1"/><line stroke="currentColor" x1="6.5" y1="1.2" x2="6.5" y2="10.3"/></svg>
                            <span class="button-text">Kayıt Ol</span>
                        </div>
                        <div class="message loadingMessage">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 17">
                                <circle class="loadingCircle" cx="2.2" cy="10" r="1.6"/>
                                <circle class="loadingCircle" cx="9.5" cy="10" r="1.6"/>
                                <circle class="loadingCircle" cx="16.8" cy="10" r="1.6"/>
                            </svg>
                        </div>
                        <div class="message successMessage">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 11"><polyline stroke="currentColor" points="1.4,5.8 5.1,9.5 11.6,2.1"/></svg>
                            <span class="button-text">Başarılı</span>
                        </div>
                    </button>
                </div>
                <div class="links">
                    <a href="login.php">Giriş Yap</a>
                    <a href="forgot_password.php">Şifremi Unuttum</a>
                </div>
            </form>
            <canvas id="canvas"></canvas>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="../assets/confetti-register-button.js"></script>
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
    </script>
</body>
</html>
