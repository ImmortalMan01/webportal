<?php
session_start();
require 'db.php';
$mods = $pdo->query('SELECT name, file FROM modules ORDER BY id')->fetchAll();
$protected = array_column($mods, 'file');
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
if (in_array($module, $protected) && !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$unreadCount = 0;
if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$_SESSION['user']]);
    $uid = $stmt->fetchColumn();
    if ($uid) {
        $q = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
        $q->execute([$uid]);
        $unreadCount = $q->fetchColumn();
    }
}
function render_menu($mods) {
    foreach ($mods as $m) {
        echo "<li class='nav-item'><a class='nav-link' href='?module=" . htmlspecialchars($m['file']) . "'>" . htmlspecialchars($m['name']) . "</a></li>";
    }
    if (isset($_SESSION['user'])) {
        echo "<li class='nav-item'><a class='nav-link' href='users.php'>Kullanıcılar</a></li>";
    }
}

function render_auth($count) {
    if (isset($_SESSION['user'])) {
        echo "<span class='navbar-text me-2'>Merhaba " . htmlspecialchars($_SESSION['user']) . "</span>";
        echo "<a class='btn btn-light btn-sm me-2' href='profile.php'>Profil</a>";
        $msg = 'Mesajlar';
        if ($count > 0) {
            $msg .= " <span class='badge bg-danger'>$count</span>";
        }
        echo "<a class='btn btn-light btn-sm me-2' href='messages.php'>$msg</a>";
        echo "<a class='btn btn-outline-light btn-sm me-2' href='logout.php'>Çıkış</a>";
        if ($_SESSION['role'] == 'admin') {
            echo "<a class='btn btn-light btn-sm' href='admin.php'>Admin Panel</a>";
        }
    } else {
        echo "<a class='btn btn-light btn-sm me-2' href='login.php'>Giriş Yap</a>";
        echo "<a class='btn btn-outline-light btn-sm' href='register.php'>Kayıt Ol</a>";
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sağlık Personeli Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sağlık Personeli Portalı</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php render_menu($mods); ?>
                </ul>
                <?php render_auth($unreadCount); ?>
            </div>
        </div>
    </nav>
    <div class="container my-4">
    <section class="card p-4">
        <?php
        if (in_array($module, $protected)) {
            $path = 'modules/' . $module . '.php';
            if (file_exists($path)) {
                include $path;
            } else {
                echo '<p>Modül bulunamadı.</p>';
            }
        } else {
            echo "<p>Hoş geldiniz! Modüllerden birini seçiniz.</p>";
        }
        ?>
    </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
