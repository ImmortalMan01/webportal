<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/settings.php';
$registrations_open = get_setting($pdo, 'registrations_open', '1');
$hide_register_button = get_setting($pdo, 'hide_register_button', '0');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($registrations_open != '1') {
        $message = 'Kayıtlar yetkililer tarafından devre dışı bırakıldı.';
    } else {
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $stmt->execute([$u]);
        if ($stmt->fetchColumn() > 0) {
            $message = 'Kullanıcı adı zaten mevcut';
        } else {
            $r = $_POST['role'] ?? 'Normal Personel';
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            $stmt->execute([$u, password_hash($p, PASSWORD_DEFAULT), $r]);
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
            <form method="post">
                <div class="inputBx">
                    <input type="text" name="username" placeholder="Kullanıcı Adı" required>
                </div>
                <div class="inputBx">
                    <input type="password" name="password" placeholder="Şifre" required>
                    <i class="toggle-password bi bi-eye"></i>
                </div>
                <div class="inputBx">
                    <select name="role">
                        <option value="Normal Personel">Normal Personel</option>
                        <option value="Sorumlu Hemşire">Sorumlu Hemşire</option>
                        <option value="Klinik Eğitim Hemşiresi">Klinik Eğitim Hemşiresi</option>
                    </select>
                </div>
                <div class="inputBx">
                    <input type="submit" value="Kayıt Ol">
                </div>
                <div class="links">
                    <a href="login.php">Giriş Yap</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/theme.js"></script>
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
