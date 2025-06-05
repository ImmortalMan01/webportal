<?php
session_start();
require __DIR__ . '/../includes/db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 login-card">
        <h2 class="text-center mb-3">Kayıt Ol</h2>
        <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>
        <form method="post">
            <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Kullanıcı Adı" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Şifre" required>
            </div>
            <div class="mb-3">
                <select name="role" class="form-select">
                    <option value="Normal Personel">Normal Personel</option>
                    <option value="Sorumlu Hemşire">Sorumlu Hemşire</option>
                    <option value="Klinik Eğitim Hemşiresi">Klinik Eğitim Hemşiresi</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
