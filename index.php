<?php
session_start();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/activity.php';
require __DIR__ . '/includes/settings.php';
if (!isset($_SESSION['user'])) {
    header('Location: landing.php');
    exit;
}
update_activity($pdo);
$registrations_open = get_setting($pdo, 'registrations_open', '1');
$hide_register_button = get_setting($pdo, 'hide_register_button', '0');
$site_name = get_setting($pdo, 'site_name', 'Sağlık Personeli Portalı');
$mods = $pdo->query('SELECT name, file FROM modules ORDER BY id')->fetchAll();
$protected = array_column($mods, 'file');
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
if (in_array($module, $protected) && !isset($_SESSION['user'])) {
    header('Location: pages/login.php');
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
        echo "<li class='nav-item'><a class='nav-link' href='pages/users.php'>Kullanıcılar</a></li>";
    }
}

function render_auth($count, $registrations_open, $hide_register_button) {
    if (isset($_SESSION['user'])) {
        echo "<span class='navbar-text me-2'>Merhaba " . htmlspecialchars($_SESSION['user']) . "</span>";
        echo "<a class='btn btn-light btn-sm me-2' href='pages/profile.php'>Profil</a>";
        $msg = 'Mesajlar';
        if ($count > 0) {
            $msg .= " <span class='badge bg-danger'>$count</span>";
        }
        echo "<a class='btn btn-light btn-sm me-2' href='pages/messages.php'>$msg</a>";
        echo "<a class='btn btn-outline-light btn-sm me-2' href='pages/logout.php'>Çıkış</a>";
        if ($_SESSION['role'] == 'admin') {
            echo "<a class='btn btn-light btn-sm' href='pages/admin.php'>Admin Panel</a>";
        }
    } else {
        echo "<a class='btn btn-light btn-sm me-2' href='pages/login.php'>Giriş Yap</a>";
        if ($registrations_open || !$hide_register_button) {
            echo "<a class='btn btn-outline-light btn-sm' href='pages/register.php'>Kayıt Ol</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo htmlspecialchars($site_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php render_menu($mods); ?>
                </ul>
                <?php render_auth($unreadCount, $registrations_open, $hide_register_button); ?>
            </div>
        </div>
    </nav>
    <div class="container my-4">
    <section class="card p-4">
        <?php
        if ($module === 'home') {
            include 'modules/home.php';
        } elseif (in_array($module, $protected)) {
            $path = 'modules/' . $module . '.php';
            if (file_exists($path)) {
                include $path;
            } else {
                echo '<p>Modül bulunamadı.</p>';
            }
        } else {
            echo '<p>Modül bulunamadı.</p>';
        }
        ?>
    </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
