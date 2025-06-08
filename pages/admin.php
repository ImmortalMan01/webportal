<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
require __DIR__ . '/../includes/settings.php';
require __DIR__ . '/../includes/roles.php';
update_activity($pdo);
$pdo->exec("CREATE TABLE IF NOT EXISTS announcements (id INT AUTO_INCREMENT PRIMARY KEY, content TEXT NOT NULL, publish_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)");
$cols = $pdo->query("SHOW COLUMNS FROM modules")->fetchAll(PDO::FETCH_COLUMN);
if(!in_array('icon',$cols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN icon VARCHAR(50) DEFAULT ''");
}
if(!in_array('description',$cols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN description VARCHAR(255) DEFAULT ''");
}
if(!in_array('color',$cols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN color VARCHAR(20) DEFAULT '#3fa7ff'");
}
if(!in_array('badge',$cols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN badge VARCHAR(50) DEFAULT ''");
}
if(!in_array('badge_class',$cols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN badge_class VARCHAR(20) DEFAULT 'badge-blue'");
}
if(!in_array('enabled',$cols)){
    $pdo->exec("ALTER TABLE modules ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1");
}
$pdo->exec("CREATE TABLE IF NOT EXISTS module_nav_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $action  = $_POST['action'] ?? '';
    if ($section && $action) {
        if ($section === 'users') {
            if ($action === 'add') {
                $u = $_POST['username'] ?? '';
                $e = $_POST['email'] ?? '';
                $p = $_POST['password'] ?? '';
                $r = $_POST['role'] ?? 'Normal Personel';
                $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
                $check->execute([$u, $e]);
                if ($check->fetchColumn() == 0) {
                    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$u, $e, password_hash($p, PASSWORD_DEFAULT), $r]);
                }
            } elseif ($action === 'changerole') {
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE username = ?');
                $stmt->execute([$_POST['role'], $_POST['username']]);
            }
        } elseif ($section === 'shifts') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO shifts (date, time) VALUES (?, ?)');
                $stmt->execute([$_POST['date'], $_POST['time']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM shifts WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'trainings') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO trainings (title, description) VALUES (?, ?)');
                $stmt->execute([$_POST['title'], $_POST['description']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM trainings WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'exams') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO exams (title, date) VALUES (?, ?)');
                $stmt->execute([$_POST['title'], $_POST['date']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM exams WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'procedures') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO procedures (name, file) VALUES (?, ?)');
                $stmt->execute([$_POST['name'], $_POST['file']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM procedures WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'modules') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO modules (name, file, icon, description, color, badge, badge_class, enabled) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([
                    $_POST['name'] ?? '',
                    $_POST['file'] ?? '',
                    $_POST['icon'] ?? '',
                    $_POST['description'] ?? '',
                    $_POST['color'] ?? '',
                    $_POST['badge'] ?? '',
                    $_POST['badge_class'] ?? '',
                    isset($_POST['enabled']) ? 1 : 0
                ]);
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE modules SET name=?, file=?, icon=?, description=?, color=?, badge=?, badge_class=?, enabled=? WHERE id=?');
                $stmt->execute([
                    $_POST['name'] ?? '',
                    $_POST['file'] ?? '',
                    $_POST['icon'] ?? '',
                    $_POST['description'] ?? '',
                    $_POST['color'] ?? '',
                    $_POST['badge'] ?? '',
                    $_POST['badge_class'] ?? '',
                    isset($_POST['enabled']) ? 1 : 0,
                    $_POST['id']
                ]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM modules WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'nav_links') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO module_nav_links (module_id,label,url) VALUES (?,?,?)');
                $stmt->execute([$_POST['module_id'], $_POST['label'], $_POST['url']]);
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE module_nav_links SET label=?, url=? WHERE id=?');
                $stmt->execute([$_POST['label'], $_POST['url'], $_POST['id']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM module_nav_links WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'pages') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO site_pages (title, slug, content) VALUES (?,?,?)');
                $stmt->execute([$_POST['title'], $_POST['slug'], $_POST['content']]);
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE site_pages SET title=?, slug=?, content=? WHERE id=?');
                $stmt->execute([$_POST['title'], $_POST['slug'], $_POST['content'], $_POST['id']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM site_pages WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'announcements') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO announcements (content) VALUES (?)');
                $stmt->execute([$_POST['content']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE announcements SET content = ? WHERE id = ?');
                $stmt->execute([$_POST['content'], $_POST['id']]);
            }
        } elseif ($section === 'experiences') {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO experiences (user_id,title,exp_date) VALUES (?,?,?)');
                $stmt->execute([$_POST['user_id'], $_POST['title'], $_POST['exp_date']]);
            } elseif ($action === 'update') {
                $stmt = $pdo->prepare('UPDATE experiences SET title=?, exp_date=? WHERE id=?');
                $stmt->execute([$_POST['title'], $_POST['exp_date'], $_POST['id']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM experiences WHERE id = ?');
                $stmt->execute([$_POST['id']]);
            }
        } elseif ($section === 'profiles') {
            if ($action === 'update') {
                $uid  = $_POST['user_id'];
                $full = $_POST['full_name'] ?? '';
                $dept = $_POST['department'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $birth = $_POST['birthdate'] ?? null;
                $stmt = $pdo->prepare('UPDATE profiles SET full_name=?, department=?, phone=?, birthdate=? WHERE user_id=?');
                $stmt->execute([$full, $dept, $phone, $birth, $uid]);
                if(isset($_POST['email'])){
                    $stmt = $pdo->prepare('UPDATE users SET email=? WHERE id=?');
                    $stmt->execute([$_POST['email'], $uid]);
                }
                if(!empty($_FILES['picture']['name'])){
                    $dir = 'uploads/';
                    if(!is_dir($dir)) mkdir($dir, 0777, true);
                    $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
                    $fname = uniqid().'.'.$ext;
                    if(move_uploaded_file($_FILES['picture']['tmp_name'], $dir.$fname)){
                        $stmt = $pdo->prepare('UPDATE profiles SET picture=? WHERE user_id=?');
                        $stmt->execute([$fname, $uid]);
                    }
                }
            }
        } elseif ($section === 'settings') {
            if ($action === 'update') {
                $reg = isset($_POST['registrations_open']) ? '1' : '0';
                $hide = isset($_POST['hide_register_button']) ? '1' : '0';
                $name = trim($_POST['site_name'] ?? '');
                $pdo->prepare('REPLACE INTO settings (name,value) VALUES ("registrations_open",?)')->execute([$reg]);
                $pdo->prepare('REPLACE INTO settings (name,value) VALUES ("hide_register_button",?)')->execute([$hide]);
                if($name !== ''){
                    $pdo->prepare('REPLACE INTO settings (name,value) VALUES ("site_name",?)')->execute([$name]);
                }
                if(!empty($_POST['role_theme']) && is_array($_POST['role_theme'])){
                    foreach($_POST['role_theme'] as $rName => $theme){
                        set_role_theme($pdo, $rName, $theme);
                    }
                }
                // SMTP settings
                $smtpFields = ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_secure','smtp_from','smtp_from_name'];
                foreach($smtpFields as $f){
                    if(isset($_POST[$f])){
                        $pdo->prepare('REPLACE INTO settings (name,value) VALUES (?,?)')->execute([$f, trim($_POST[$f])]);
                    }
                }
            }
        } elseif ($section === 'admin_messages') {
            if ($action === 'send') {
                $from = $_POST['from'] ?? '';
                $u1 = $_POST['u1'] ?? '';
                $u2 = $_POST['u2'] ?? '';
                $to   = $_POST['to']   ?? '';
                $text = trim($_POST['message'] ?? '');
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
                $stmt->execute([$from]);
                $sid = $stmt->fetchColumn();
                if($from === $_SESSION['user']){
                    $stmt->execute([$to]);
                } else {
                    $stmt->execute([$from==$u1?$u2:$u1]);
                }
                $rid = $stmt->fetchColumn();
                if($sid && $rid && $text!=''){
                    $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)')->execute([$sid,$rid,$text]);
                }
            }
        }
        log_activity($pdo, 'admin_' . $section . '_' . $action);
        if($section==='admin_messages'){
            header('Location: admin.php?u1=' . urlencode($u1) . '&u2=' . urlencode($u2) . '#messages');
        } else {
            $anchor = $section==='nav_links' ? 'modules' : $section;
            header('Location: admin.php#' . $anchor);
        }
        exit;
    }
}

$stmt = $pdo->query('SELECT id, username, email, role FROM users ORDER BY username');
$users = $stmt->fetchAll();
$shifts = $pdo->query('SELECT id, date, time FROM shifts ORDER BY date')->fetchAll();
$trainings = $pdo->query('SELECT id, title, description FROM trainings ORDER BY id')->fetchAll();
$exams = $pdo->query('SELECT id, title, date FROM exams ORDER BY date')->fetchAll();
$procedures = $pdo->query('SELECT id, name, file FROM procedures ORDER BY name')->fetchAll();
$modules = $pdo->query('SELECT id, name, file, icon, description, color, badge, badge_class, enabled FROM modules ORDER BY id')->fetchAll();
$nav_links_raw = $pdo->query('SELECT id, module_id, label, url FROM module_nav_links ORDER BY id')->fetchAll();
$module_navs = [];
foreach($nav_links_raw as $nl){
    $module_navs[$nl['module_id']][] = $nl;
}
$site_pages = $pdo->query('SELECT id, slug, title, content FROM site_pages ORDER BY id')->fetchAll();
$announcements = $pdo->query('SELECT id, content, publish_date FROM announcements ORDER BY publish_date DESC')->fetchAll();
$experiences = $pdo->query('SELECT e.id, e.user_id, u.username, e.title, e.exp_date FROM experiences e JOIN users u ON e.user_id=u.id ORDER BY e.exp_date DESC')->fetchAll();
$profiles = $pdo->query('SELECT p.user_id, p.full_name, p.department, p.phone, p.birthdate, p.picture, u.email FROM profiles p JOIN users u ON p.user_id=u.id ORDER BY p.user_id')->fetchAll();
$settings = $pdo->query('SELECT name,value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$registrations_open = $settings['registrations_open'] ?? '1';
$hide_register_button = $settings['hide_register_button'] ?? '0';
$site_name = $settings['site_name'] ?? 'Sağlık Personeli Portalı';
$smtp_host = $settings['smtp_host'] ?? '';
$smtp_port = $settings['smtp_port'] ?? '';
$smtp_user = $settings['smtp_user'] ?? '';
$smtp_pass = $settings['smtp_pass'] ?? '';
$smtp_secure = $settings['smtp_secure'] ?? '';
$smtp_from = $settings['smtp_from'] ?? '';
$smtp_from_name = $settings['smtp_from_name'] ?? '';
$roles = get_all_roles($pdo);
$role_themes = [];
foreach($roles as $r){
    $role_themes[$r] = get_role_theme($pdo, $r);
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
        echo "      <li class='drop-down__item'><a href='profile.php'><i class='fa-solid fa-user drop-down__item-icon'></i><span class='drop-down__item-text'>Profil</span></a></li>";
        $msg = 'Mesajlar';
        if ($count > 0) { $msg .= " <span class=\'badge bg-danger\'>$count</span>"; }
        echo "      <li class='drop-down__item'><a href='messages.php'><i class='fa-solid fa-envelope drop-down__item-icon'></i><span class='drop-down__item-text'>$msg</span></a></li>";
        if ($_SESSION['role'] == 'admin') {
            echo "      <li class='drop-down__item'><a href='admin.php'><i class='fa-solid fa-toolbox drop-down__item-icon'></i><span class='drop-down__item-text'>Admin Paneli</span></a></li>";
        }
        echo "    </ul>";
        echo "  </div>";
        echo "</div>";
        echo "<a class='btn btn-outline-light btn-sm me-2' href='logout.php'>Çıkış</a>";
    } else {
        echo "<a class='btn btn-light btn-sm me-2' href='login.php'>Giriş Yap</a>";
        if ($registrations_open || !$hide_register_button) {
            echo "<a class='btn btn-outline-light btn-sm' href='register.php'>Kayıt Ol</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/user-dropdown.css">
</head>
<body class="admin-layout">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><?php echo htmlspecialchars($site_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Ana Sayfa</a></li>
                </ul>
                <?php render_auth($unreadCount, $registrations_open, $hide_register_button); ?>
                <button id="themeToggleGlobal" class="btn btn-outline-light btn-sm ms-2" type="button">🌙</button>
            </div>
        </div>
    </nav>
    <nav class="admin-sidebar nav flex-column" id="adminTab" role="tablist">
        <a class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" href="#users" role="tab">Kullanıcılar</a>
        <a class="nav-link" id="shifts-tab" data-bs-toggle="tab" data-bs-target="#shifts" href="#shifts" role="tab">Çalışma Listesi</a>
        <a class="nav-link" id="trainings-tab" data-bs-toggle="tab" data-bs-target="#trainings" href="#trainings" role="tab">Eğitimler</a>
        <a class="nav-link" id="exams-tab" data-bs-toggle="tab" data-bs-target="#exams" href="#exams" role="tab">Sınavlar</a>
        <a class="nav-link" id="procedures-tab" data-bs-toggle="tab" data-bs-target="#procedures" href="#procedures" role="tab">Prosedürler</a>
        <a class="nav-link" id="modules-tab" data-bs-toggle="tab" data-bs-target="#modules" href="#modules" role="tab">Modüller</a>
        <a class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" href="#pages" role="tab">Sayfalar</a>
        <a class="nav-link" id="announcements-tab" data-bs-toggle="tab" data-bs-target="#announcements" href="#announcements" role="tab">Duyurular</a>
        <a class="nav-link" id="experiences-tab" data-bs-toggle="tab" data-bs-target="#experiences" href="#experiences" role="tab">Deneyimler</a>
        <a class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" href="#messages" role="tab">Mesajlar</a>
        <a class="nav-link" id="profiles-tab" data-bs-toggle="tab" data-bs-target="#profiles" href="#profiles" role="tab">Profiller</a>
        <a class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" href="#settings" role="tab">Ayarlar</a>
    </nav>
    <main class="admin-content">
        <h2 class="mb-4">Admin Panel</h2>

        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="users" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="users">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-2"><input type="text" name="username" class="form-control" placeholder="Kullanıcı" required></div>
                    <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="E-posta" required></div>
                    <div class="col-md-2"><input type="password" name="password" class="form-control" placeholder="Şifre" required></div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <?php foreach (default_roles() as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <tr><th>Kullanıcı</th><th>E-posta</th><th>Rol</th><th>Değiştir</th></tr>
                    <?php foreach ($users as $info): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($info['username']); ?></td>
                            <td><?php echo htmlspecialchars($info['email']); ?></td>
                            <td><?php echo htmlspecialchars($info['role']); ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="section" value="users">
                                    <input type="hidden" name="action" value="changerole">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($info['username']); ?>">
                                    <select name="role" class="form-select form-select-sm me-2">
                                        <?php foreach (default_roles() as $r): ?>
                                        <option value="<?php echo htmlspecialchars($r); ?>" <?php if ($info['role']==$r) echo 'selected'; ?>><?php echo htmlspecialchars($r); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-secondary">Kaydet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
            </div>
            <div class="tab-pane fade" id="shifts" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="shifts">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4"><input type="date" name="date" class="form-control" required></div>
                    <div class="col-md-4"><input type="text" name="time" class="form-control" placeholder="Vardiya" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <tr><th>Tarih</th><th>Vardiya</th><th></th></tr>
                    <?php foreach ($shifts as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['date']); ?></td>
                            <td><?php echo htmlspecialchars($s['time']); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="section" value="shifts">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                    <button class="btn btn-sm btn-danger">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
            </div>
            <div class="tab-pane fade" id="trainings" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="trainings">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4"><input type="text" name="title" class="form-control" placeholder="Başlık" required></div>
                    <div class="col-md-4"><input type="text" name="description" class="form-control" placeholder="Açıklama" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <ul class="list-group">
                    <?php foreach ($trainings as $t): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong><?php echo htmlspecialchars($t['title']); ?></strong> - <?php echo htmlspecialchars($t['description']); ?></span>
                            <form method="post" class="ms-3">
                                <input type="hidden" name="section" value="trainings">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                <button class="btn btn-sm btn-danger">Sil</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="tab-pane fade" id="exams" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="exams">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4"><input type="text" name="title" class="form-control" placeholder="Sınav" required></div>
                    <div class="col-md-4"><input type="date" name="date" class="form-control" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <tr><th>Sınav</th><th>Tarih</th><th></th></tr>
                    <?php foreach ($exams as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['title']); ?></td>
                            <td><?php echo htmlspecialchars($e['date']); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="section" value="exams">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                    <button class="btn btn-sm btn-danger">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
            </div>
            <div class="tab-pane fade" id="procedures" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="procedures">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Prosedür" required></div>
                    <div class="col-md-4"><input type="text" name="file" class="form-control" placeholder="Dosya" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <ul class="list-group">
                    <?php foreach ($procedures as $p): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="docs/<?php echo htmlspecialchars($p['file']); ?>" target="_blank"><?php echo htmlspecialchars($p['name']); ?></a>
                            <form method="post" class="ms-3">
                                <input type="hidden" name="section" value="procedures">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <button class="btn btn-sm btn-danger">Sil</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="tab-pane fade" id="modules" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="modules">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-2"><input type="text" name="name" class="form-control" placeholder="Başlık" required></div>
                    <div class="col-md-2"><input type="text" name="file" class="form-control" placeholder="Dosya" required></div>
                    <div class="col-md-2"><input type="text" name="icon" class="form-control" placeholder="Icon class"></div>
                    <div class="col-md-1"><input type="text" name="color" class="form-control" placeholder="Renk #"></div>
                    <div class="col-md-1"><input type="text" name="badge" class="form-control" placeholder="Badge"></div>
                    <div class="col-md-2">
                        <select name="badge_class" class="form-select">
                            <option value="badge-green">Yeşil</option>
                            <option value="badge-blue">Mavi</option>
                            <option value="badge-orange">Turuncu</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enabled" id="modEnabledAdd" checked>
                            <label class="form-check-label" for="modEnabledAdd">Göster</label>
                        </div>
                    </div>
                    <div class="col-12 mt-2"><input type="text" name="description" class="form-control" placeholder="Açıklama"></div>
                    <div class="col-12 text-end mt-2"><button class="btn btn-primary">Ekle</button></div>
                </form>
                <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <tr><th>Başlık</th><th>Dosya</th><th>Icon</th><th>Renk</th><th>Badge</th><th>Sınıf</th><th>Açıklama</th><th>Göster</th><th></th></tr>
                    <?php foreach ($modules as $m): ?>
                        <tr>
                            <form method="post" class="d-flex flex-wrap align-items-center">
                                <input type="hidden" name="section" value="modules">
                                <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                <td><input type="text" name="name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['name']); ?>"></td>
                                <td><input type="text" name="file" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['file']); ?>"></td>
                                <td><input type="text" name="icon" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['icon']); ?>"></td>
                                <td><input type="text" name="color" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['color']); ?>"></td>
                                <td><input type="text" name="badge" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['badge']); ?>"></td>
                                <td>
                                    <select name="badge_class" class="form-select form-select-sm">
                                        <option value="badge-green" <?php if($m['badge_class']=='badge-green') echo 'selected'; ?>>Yeşil</option>
                                        <option value="badge-blue" <?php if($m['badge_class']=='badge-blue') echo 'selected'; ?>>Mavi</option>
                                        <option value="badge-orange" <?php if($m['badge_class']=='badge-orange') echo 'selected'; ?>>Turuncu</option>
                                    </select>
                                </td>
                                <td><input type="text" name="description" class="form-control form-control-sm" value="<?php echo htmlspecialchars($m['description']); ?>"></td>
                                <td class="text-center"><input type="checkbox" class="form-check-input" name="enabled" value="1" <?php echo $m['enabled']? 'checked':''; ?>></td>
                                <td>
                                    <button name="action" value="update" class="btn btn-sm btn-secondary me-1">Kaydet</button>
                                    <button name="action" value="delete" class="btn btn-sm btn-danger me-1">Sil</button>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#navModal<?php echo $m['id']; ?>">Başlıklar</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
                <?php foreach($modules as $m): ?>
                <div class="modal fade" id="navModal<?php echo $m['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo htmlspecialchars($m['name']); ?> Başlıkları</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post" class="row g-2 mb-3">
                                    <input type="hidden" name="section" value="nav_links">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="module_id" value="<?php echo $m['id']; ?>">
                                    <div class="col-5"><input type="text" name="label" class="form-control form-control-sm" placeholder="Etiket"></div>
                                    <div class="col-5"><input type="text" name="url" class="form-control form-control-sm" placeholder="URL"></div>
                                    <div class="col-2"><button class="btn btn-sm btn-primary w-100">Ekle</button></div>
                                </form>
                <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr><th>Etiket</th><th>URL</th><th></th></tr>
                                    <?php foreach($module_navs[$m['id']] ?? [] as $ln): ?>
                                    <tr>
                                        <form method="post" class="d-flex flex-wrap align-items-center">
                                            <input type="hidden" name="section" value="nav_links">
                                            <input type="hidden" name="id" value="<?php echo $ln['id']; ?>">
                                            <td><input type="text" name="label" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ln['label']); ?>"></td>
                                            <td><input type="text" name="url" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ln['url']); ?>"></td>
                                            <td>
                                                <button name="action" value="update" class="btn btn-sm btn-secondary me-1">Kaydet</button>
                                                <button name="action" value="delete" class="btn btn-sm btn-danger">Sil</button>
                                            </td>
                                        </form>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="tab-pane fade" id="pages" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="pages">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-3"><input type="text" name="title" class="form-control" placeholder="Başlık" required></div>
                    <div class="col-md-3"><input type="text" name="slug" class="form-control" placeholder="slug" required></div>
                    <div class="col-md-4"><input type="text" name="content" class="form-control" placeholder="İçerik" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <tr><th>Başlık</th><th>Slug</th><th>İçerik</th><th></th></tr>
                    <?php foreach($site_pages as $pg): ?>
                        <tr>
                            <form method="post" class="d-flex">
                                <input type="hidden" name="section" value="pages">
                                <input type="hidden" name="id" value="<?php echo $pg['id']; ?>">
                                <td><input type="text" name="title" class="form-control form-control-sm" value="<?php echo htmlspecialchars($pg['title']); ?>"></td>
                                <td><input type="text" name="slug" class="form-control form-control-sm" value="<?php echo htmlspecialchars($pg['slug']); ?>"></td>
                                <td><textarea name="content" class="form-control form-control-sm" rows="1"><?php echo htmlspecialchars($pg['content']); ?></textarea></td>
                                <td>
                                    <button name="action" value="update" class="btn btn-sm btn-secondary me-1">Kaydet</button>
                                    <button name="action" value="delete" class="btn btn-sm btn-danger">Sil</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
            </div>
            <div class="tab-pane fade" id="announcements" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="announcements">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-10"><input type="text" name="content" class="form-control" placeholder="Duyuru" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <ul class="list-group">
                    <?php foreach($announcements as $an): ?>
                        <li class="list-group-item">
                            <form method="post" class="d-flex flex-wrap align-items-center">
                                <input type="hidden" name="section" value="announcements">
                                <input type="hidden" name="id" value="<?php echo $an['id']; ?>">
                                <input type="text" name="content" class="form-control form-control-sm flex-grow-1 me-2 mb-2" value="<?php echo htmlspecialchars($an['content']); ?>">
                                <small class="text-muted me-2 mb-2"><?php echo $an['publish_date']; ?></small>
                                <button name="action" value="update" class="btn btn-sm btn-secondary me-1 mb-2">Kaydet</button>
                                <button name="action" value="delete" class="btn btn-sm btn-danger mb-2">Sil</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="tab-pane fade" id="experiences" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="experiences">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-3">
                        <select name="user_id" class="form-select" required>
                            <option value="">Kullanıcı</option>
                            <?php foreach($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5"><input type="text" name="title" class="form-control" placeholder="Deneyim" required></div>
                    <div class="col-md-2"><input type="date" name="exp_date" class="form-control"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <tr><th>Kullanıcı</th><th>Deneyim</th><th>Tarih</th><th></th></tr>
                    <?php foreach($experiences as $ex): ?>
                        <tr>
                            <form method="post" class="d-flex align-items-center">
                                <input type="hidden" name="section" value="experiences">
                                <input type="hidden" name="id" value="<?php echo $ex['id']; ?>">
                                <td class="align-middle"><?php echo htmlspecialchars($ex['username']); ?></td>
                                <td><input type="text" name="title" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ex['title']); ?>"></td>
                                <td><input type="date" name="exp_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ex['exp_date']); ?>"></td>
                                <td>
                                    <button name="action" value="update" class="btn btn-sm btn-secondary me-1">Kaydet</button>
                                    <button name="action" value="delete" class="btn btn-sm btn-danger">Sil</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
            </div>
            <div class="tab-pane fade" id="messages" role="tabpanel">
                <form method="get" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="messages">
                    <div class="col-md-3">
                        <select name="u1" class="form-select" required>
                            <option value="">Kullanıcı 1</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo htmlspecialchars($u['username']); ?>" <?php if(isset($_GET['u1']) && $_GET['u1']==$u['username']) echo 'selected'; ?>><?php echo htmlspecialchars($u['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="u2" class="form-select" required>
                            <option value="">Kullanıcı 2</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo htmlspecialchars($u['username']); ?>" <?php if(isset($_GET['u2']) && $_GET['u2']==$u['username']) echo 'selected'; ?>><?php echo htmlspecialchars($u['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Göster</button></div>
                </form>
                <?php
                    $u1 = $_GET['u1'] ?? '';
                    $u2 = $_GET['u2'] ?? '';
                    $conv = [];
                    if($u1 && $u2){
                        $id1 = $pdo->prepare('SELECT id FROM users WHERE username=?');
                        $id1->execute([$u1]);
                        $id1 = $id1->fetchColumn();
                        $id2 = $pdo->prepare('SELECT id FROM users WHERE username=?');
                        $id2->execute([$u2]);
                        $id2 = $id2->fetchColumn();
                        $adminIdStmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
                        $adminIdStmt->execute([$_SESSION['user']]);
                        $adminId = $adminIdStmt->fetchColumn();
                        if($id1 && $id2 && $adminId){
                            $q = $pdo->prepare('SELECT m.sender_id, m.receiver_id, m.message, m.created_at, u.role FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.sender_id IN (?,?,?) AND m.receiver_id IN (?,?,?) ORDER BY m.id');
                            $q->execute([$id1,$id2,$adminId,$id1,$id2,$adminId]);
                            $conv = $q->fetchAll();
                        }
                    }
                ?>
                <div class="border p-2 mb-3" style="max-height:300px;overflow-y:auto;">
                <?php foreach($conv as $m): ?>
                    <div class="d-flex <?php echo $m['sender_id']==$id1? 'justify-content-start':'justify-content-end'; ?>">
                        <div class="bubble <?php echo $m['sender_id']==$id1? 'theirs':'mine'; ?><?php if($m['role']==='admin') echo ' admin-msg'; ?>">
                            <div class="text"><?php echo htmlspecialchars($m['message']); ?></div>
                            <div class="meta small text-muted"><?php echo $m['created_at']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                <?php if($u1 && $u2): ?>
                <form method="post" class="d-flex flex-wrap align-items-start" id="adminMsgForm">
                    <input type="hidden" name="section" value="admin_messages">
                    <input type="hidden" name="action" value="send">
                    <input type="hidden" name="u1" value="<?php echo htmlspecialchars($u1); ?>">
                    <input type="hidden" name="u2" value="<?php echo htmlspecialchars($u2); ?>">
                    <select name="from" id="fromSelect" class="form-select me-md-2 mb-2 flex-grow-1" style="max-width:150px;">
                        <option value="<?php echo htmlspecialchars($u1); ?>"><?php echo htmlspecialchars($u1); ?> olarak</option>
                        <option value="<?php echo htmlspecialchars($u2); ?>"><?php echo htmlspecialchars($u2); ?> olarak</option>
                        <option value="<?php echo htmlspecialchars($_SESSION['user']); ?>">Admin olarak</option>
                    </select>
                    <select name="to" id="toSelect" class="form-select me-md-2 mb-2 flex-grow-1" style="max-width:150px; display:none;">
                        <option value="<?php echo htmlspecialchars($u1); ?>"><?php echo htmlspecialchars($u1); ?></option>
                        <option value="<?php echo htmlspecialchars($u2); ?>"><?php echo htmlspecialchars($u2); ?></option>
                    </select>
                    <input type="text" name="message" class="form-control me-md-2 mb-2 flex-grow-1" required>
                    <button class="btn btn-primary mb-2">Gönder</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="profiles" role="tabpanel">
                <div class="table-responsive">
                <table class="table table-sm">
                    <tr><th>Kullanıcı ID</th><th>Ad Soyad</th><th>Birim</th><th>Telefon</th><th>Doğum</th><th></th><th></th></tr>
                    <?php foreach ($profiles as $p): ?>
                        <tr>
                            <form method="post" class="d-flex">
                                <input type="hidden" name="section" value="profiles">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="user_id" value="<?php echo $p['user_id']; ?>">
                                <td class="align-middle"><?php echo $p['user_id']; ?></td>
                                <td><input type="text" name="full_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['full_name']); ?>"></td>
                                <td><input type="text" name="department" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['department']); ?>"></td>
                                <td><input type="text" name="phone" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['phone']); ?>"></td>
                                <td><input type="date" name="birthdate" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['birthdate']); ?>"></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary">Kaydet</button>
                                    <button type="button" class="btn btn-sm btn-info ms-1" data-bs-toggle="modal" data-bs-target="#profileModal<?php echo $p['user_id']; ?>">Detaylar</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </table>
</div>
<?php foreach($profiles as $p): ?>
<div class="modal fade" id="profileModal<?php echo $p['user_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Profil Detayları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data" class="row g-2">
                    <input type="hidden" name="section" value="profiles">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo $p['user_id']; ?>">
                    <div class="col-md-6">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['full_name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['email']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Birim</label>
                        <input type="text" name="department" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['department']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['phone']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Doğum</label>
                        <input type="date" name="birthdate" class="form-control form-control-sm" value="<?php echo htmlspecialchars($p['birthdate']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profil Resmi</label><br>
                        <img src="<?php echo $p['picture'] ? 'uploads/' . htmlspecialchars($p['picture']) : '../assets/profil.png'; ?>" width="80" class="mb-2">
                        <input type="file" name="picture" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
            </div>
            <div class="tab-pane fade" id="settings" role="tabpanel">
                <form method="post" class="mb-3">
                    <input type="hidden" name="section" value="settings">
                    <input type="hidden" name="action" value="update">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Web Site Adı</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>">
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="registrations_open" name="registrations_open" value="1" <?php if($registrations_open=='1') echo 'checked'; ?>>
                        <label class="form-check-label" for="registrations_open">Kayıtları Aç</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="hide_register_button" name="hide_register_button" value="1" <?php if($hide_register_button=='1') echo 'checked'; ?>>
                        <label class="form-check-label" for="hide_register_button">Kayıt Ol butonunu gizle</label>
                    </div>
                    <h5 class="mt-4">Rol Temaları</h5>
                    <?php foreach($roles as $r): ?>
                    <div class="mb-2">
                        <label class="form-label"><?php echo htmlspecialchars($r); ?></label>
                        <select name="role_theme[<?php echo htmlspecialchars($r); ?>]" class="form-select">
                            <option value="classic" <?php if(($role_themes[$r] ?? 'classic')==='classic') echo 'selected'; ?>>Klasik</option>
                            <option value="dashboard" <?php if(($role_themes[$r] ?? 'classic')==='dashboard') echo 'selected'; ?>>Dashboard</option>
                        </select>
                    </div>
                    <?php endforeach; ?>
                    <h5 class="mt-4">SMTP Ayarları</h5>
                    <div class="mb-2">
                        <label class="form-label" for="smtp_host">Sunucu</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="smtp_port">Port</label>
                        <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="smtp_user">Kullanıcı</label>
                        <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($smtp_user); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="smtp_pass">Şifre</label>
                        <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars($smtp_pass); ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="smtp_secure">Güvenlik</label>
                        <select class="form-select" id="smtp_secure" name="smtp_secure">
                            <option value="" <?php if($smtp_secure=='') echo 'selected'; ?>>Yok</option>
                            <option value="ssl" <?php if($smtp_secure=='ssl') echo 'selected'; ?>>SSL</option>
                            <option value="tls" <?php if($smtp_secure=='tls') echo 'selected'; ?>>TLS</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="smtp_from">Gönderen E-posta</label>
                        <input type="text" class="form-control" id="smtp_from" name="smtp_from" value="<?php echo htmlspecialchars($smtp_from); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="smtp_from_name">Gönderen Adı</label>
                        <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?php echo htmlspecialchars($smtp_from_name); ?>">
                    </div>
                    <button class="btn btn-primary">Kaydet</button>
                </form>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/theme.js"></script>
    <script src="../assets/user-dropdown.js"></script>
    <script>
        const hash = location.hash;
        if(hash){
            const trigger = document.querySelector(`#adminTab [data-bs-target="${hash}"]`);
            if(trigger){
                new bootstrap.Tab(trigger).show();
            }
        }
        document.querySelectorAll('#adminTab [data-bs-toggle="tab"]').forEach(link=>{
            link.addEventListener('shown.bs.tab', e=>{
                history.replaceState(null,null,e.target.dataset.bsTarget);
            });
        });
        const fromSel = document.getElementById('fromSelect');
        const toSel = document.getElementById('toSelect');
        function toggleTo(){
            if(fromSel && toSel){
                toSel.style.display = fromSel.value === '<?php echo $_SESSION['user']; ?>' ? 'block' : 'none';
            }
        }
        toggleTo();
        if(fromSel) fromSel.addEventListener('change', toggleTo);

        const regOpenChk = document.getElementById('registrations_open');
        const hideRegBtnChk = document.getElementById('hide_register_button');
        function toggleHideOption(){
            if(regOpenChk && hideRegBtnChk){
                if(regOpenChk.checked){
                    hideRegBtnChk.checked = false;
                    hideRegBtnChk.disabled = true;
                }else{
                    hideRegBtnChk.disabled = false;
                }
            }
        }
        toggleHideOption();
        if(regOpenChk) regOpenChk.addEventListener('change', toggleHideOption);
    </script>
</body>
</html>
