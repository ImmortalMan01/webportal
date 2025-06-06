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
$role = $_SESSION['role'] ?? 'guest';
$theme = get_role_theme($pdo, $role);
$modCols = $pdo->query("SHOW COLUMNS FROM modules")->fetchAll(PDO::FETCH_COLUMN);
if(!in_array('enabled',$modCols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1");
}
$pdo->exec("CREATE TABLE IF NOT EXISTS module_nav_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
)");
$allMods = $pdo->query('SELECT name, file, enabled FROM modules ORDER BY id')->fetchAll();
$mods = array_filter($allMods, fn($m)=>$m['enabled']);
$protected = array_column($allMods, 'file');
$module = isset($_GET['module']) ? $_GET['module'] : 'home';

$stmt = $pdo->prepare('SELECT id FROM modules WHERE file=?');
$stmt->execute([$module]);
$currentModuleId = $stmt->fetchColumn();
$moduleNav = [];
if($currentModuleId){
    $ns = $pdo->prepare('SELECT label,url FROM module_nav_links WHERE module_id=? ORDER BY id');
    $ns->execute([$currentModuleId]);
    $moduleNav = $ns->fetchAll();
}
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
function render_menu($mods, $extra = []) {
    echo "<li class='nav-item'><a class='nav-link' href='index.php'>Ana Sayfa</a></li>";
    foreach ($mods as $m) {
        echo "<li class='nav-item'><a class='nav-link' href='?module=" . htmlspecialchars($m['file']) . "'>" . htmlspecialchars($m['name']) . "</a></li>";
    }
    foreach ($extra as $e) {
        echo "<li class='nav-item'><a class='nav-link' href='" . htmlspecialchars($e['url']) . "'>" . htmlspecialchars($e['label']) . "</a></li>";
    }
    if (isset($_SESSION['user'])) {
        echo "<li class='nav-item'><a class='nav-link' href='pages/users.php'>Kullanıcılar</a></li>";
    }
}

function render_auth($count, $registrations_open, $hide_register_button) {
    if (isset($_SESSION['user'])) {
        echo "<span class='navbar-text me-2'>Merhaba " . htmlspecialchars($_SESSION['user']) . "</span>";
        echo "<div class='drop-down me-2'>";
        echo "  <div id='dropDown' class='drop-down__button'>";
        echo "    <span class='drop-down__name'>Ayarlar</span>";
        echo "    <i class='fa-solid fa-gear drop-down__icon'></i>";
        echo "  </div>";
        echo "  <div class='drop-down__menu-box'>";
        echo "    <ul class='drop-down__menu'>";
        echo "      <li class='drop-down__item'><a href='pages/profile.php'><i class='fa-solid fa-user drop-down__item-icon'></i><span class='drop-down__item-text'>Profil</span></a></li>";
        $msg = 'Mesajlar';
        if ($count > 0) { $msg .= " <span class=\'badge bg-danger\'>$count</span>"; }
        echo "      <li class='drop-down__item'><a href='pages/messages.php'><i class='fa-solid fa-envelope drop-down__item-icon'></i><span class='drop-down__item-text'>$msg</span></a></li>";
        if ($_SESSION['role'] == 'admin') {
            echo "      <li class='drop-down__item'><a href='pages/admin.php'><i class='fa-solid fa-toolbox drop-down__item-icon'></i><span class='drop-down__item-text'>Admin Paneli</span></a></li>";
        }
        echo "    </ul>";
        echo "  </div>";
        echo "</div>";
        echo "<a class='btn btn-outline-light btn-sm me-2' href='pages/logout.php'>Çıkış</a>";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if($theme === 'dashboard'): ?>
    <link rel="stylesheet" href="assets/dashboard.css">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/user-dropdown.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo htmlspecialchars($site_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php render_menu($mods, $moduleNav); ?>
                </ul>
                <?php render_auth($unreadCount, $registrations_open, $hide_register_button); ?>
                <button id="themeToggleGlobal" class="btn btn-outline-light btn-sm ms-2" type="button">🌙</button>
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
    <script src="assets/theme.js"></script>
    <script src="assets/user-dropdown.js"></script>
</body>
</html>
