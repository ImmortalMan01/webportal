<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
update_activity($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $action  = $_POST['action'] ?? '';
    if ($section && $action) {
        if ($section === 'users') {
            if ($action === 'add') {
                $u = $_POST['username'] ?? '';
                $p = $_POST['password'] ?? '';
                $r = $_POST['role'] ?? 'Normal Personel';
                $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
                $check->execute([$u]);
                if ($check->fetchColumn() == 0) {
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                    $stmt->execute([$u, password_hash($p, PASSWORD_DEFAULT), $r]);
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
                $stmt = $pdo->prepare('INSERT INTO modules (name, file) VALUES (?, ?)');
                $stmt->execute([$_POST['name'], $_POST['file']]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM modules WHERE id = ?');
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
                $stmt = $pdo->prepare('UPDATE profiles SET full_name=?, department=?, phone=?, birthdate=? WHERE user_id=?');
                $stmt->execute([$_POST['full_name'], $_POST['department'], $_POST['phone'], $_POST['birthdate'], $_POST['user_id']]);
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
        if($section==='admin_messages'){
            header('Location: admin.php?u1=' . urlencode($u1) . '&u2=' . urlencode($u2) . '#messages');
        } else {
            header('Location: admin.php#' . $section);
        }
        exit;
    }
}

$stmt = $pdo->query('SELECT id, username, role FROM users ORDER BY username');
$users = $stmt->fetchAll();
$shifts = $pdo->query('SELECT id, date, time FROM shifts ORDER BY date')->fetchAll();
$trainings = $pdo->query('SELECT id, title, description FROM trainings ORDER BY id')->fetchAll();
$exams = $pdo->query('SELECT id, title, date FROM exams ORDER BY date')->fetchAll();
$procedures = $pdo->query('SELECT id, name, file FROM procedures ORDER BY name')->fetchAll();
$modules = $pdo->query('SELECT id, name, file FROM modules ORDER BY id')->fetchAll();
$site_pages = $pdo->query('SELECT id, slug, title, content FROM site_pages ORDER BY id')->fetchAll();
$experiences = $pdo->query('SELECT e.id, e.user_id, u.username, e.title, e.exp_date FROM experiences e JOIN users u ON e.user_id=u.id ORDER BY e.exp_date DESC')->fetchAll();
$profiles = $pdo->query('SELECT user_id, full_name, department, phone, birthdate FROM profiles')->fetchAll();
$settings = $pdo->query('SELECT name,value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$registrations_open = $settings['registrations_open'] ?? '1';
$hide_register_button = $settings['hide_register_button'] ?? '0';
$site_name = $settings['site_name'] ?? 'Sağlık Personeli Portalı';
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container my-4">
        <h2 class="mb-4">Admin Panel</h2>
        <ul class="nav nav-tabs mb-3" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Kullanıcılar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shifts-tab" data-bs-toggle="tab" data-bs-target="#shifts" type="button" role="tab">Vardiyalar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="trainings-tab" data-bs-toggle="tab" data-bs-target="#trainings" type="button" role="tab">Eğitimler</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="exams-tab" data-bs-toggle="tab" data-bs-target="#exams" type="button" role="tab">Sınavlar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="procedures-tab" data-bs-toggle="tab" data-bs-target="#procedures" type="button" role="tab">Prosedürler</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="modules-tab" data-bs-toggle="tab" data-bs-target="#modules" type="button" role="tab">Modüller</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab">Sayfalar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="experiences-tab" data-bs-toggle="tab" data-bs-target="#experiences" type="button" role="tab">Deneyimler</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">Mesajlar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profiles-tab" data-bs-toggle="tab" data-bs-target="#profiles" type="button" role="tab">Profiller</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Ayarlar</button>
            </li>
        </ul>
        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="users" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="users">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Kullanıcı" required></div>
                    <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Şifre" required></div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="Normal Personel">Normal Personel</option>
                            <option value="admin">admin</option>
                            <option value="Sorumlu Hemşire">Sorumlu Hemşire</option>
                            <option value="Klinik Eğitim Hemşiresi">Klinik Eğitim Hemşiresi</option>
                        </select>
                    </div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <table class="table table-sm table-striped">
                    <tr><th>Kullanıcı</th><th>Rol</th><th>Değiştir</th></tr>
                    <?php foreach ($users as $info): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($info['username']); ?></td>
                            <td><?php echo htmlspecialchars($info['role']); ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="section" value="users">
                                    <input type="hidden" name="action" value="changerole">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($info['username']); ?>">
                                    <select name="role" class="form-select form-select-sm me-2">
                                        <option value="Normal Personel" <?php if ($info['role']=='Normal Personel') echo 'selected'; ?>>Normal Personel</option>
                                        <option value="admin" <?php if ($info['role']=='admin') echo 'selected'; ?>>admin</option>
                                        <option value="Sorumlu Hemşire" <?php if ($info['role']=='Sorumlu Hemşire') echo 'selected'; ?>>Sorumlu Hemşire</option>
                                        <option value="Klinik Eğitim Hemşiresi" <?php if ($info['role']=='Klinik Eğitim Hemşiresi') echo 'selected'; ?>>Klinik Eğitim Hemşiresi</option>
                                    </select>
                                    <button class="btn btn-sm btn-secondary">Kaydet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="tab-pane fade" id="shifts" role="tabpanel">
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="section" value="shifts">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-4"><input type="date" name="date" class="form-control" required></div>
                    <div class="col-md-4"><input type="text" name="time" class="form-control" placeholder="Vardiya" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
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
                    <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Başlık" required></div>
                    <div class="col-md-4"><input type="text" name="file" class="form-control" placeholder="Dosya" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ekle</button></div>
                </form>
                <ul class="list-group">
                    <?php foreach ($modules as $m): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($m['name']); ?> (<?php echo htmlspecialchars($m['file']); ?>)
                            <form method="post" class="ms-3">
                                <input type="hidden" name="section" value="modules">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                <button class="btn btn-sm btn-danger">Sil</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
                <form method="post" class="d-flex align-items-start" id="adminMsgForm">
                    <input type="hidden" name="section" value="admin_messages">
                    <input type="hidden" name="action" value="send">
                    <input type="hidden" name="u1" value="<?php echo htmlspecialchars($u1); ?>">
                    <input type="hidden" name="u2" value="<?php echo htmlspecialchars($u2); ?>">
                    <select name="from" id="fromSelect" class="form-select me-2" style="max-width:150px;">
                        <option value="<?php echo htmlspecialchars($u1); ?>"><?php echo htmlspecialchars($u1); ?> olarak</option>
                        <option value="<?php echo htmlspecialchars($u2); ?>"><?php echo htmlspecialchars($u2); ?> olarak</option>
                        <option value="<?php echo htmlspecialchars($_SESSION['user']); ?>">Admin olarak</option>
                    </select>
                    <select name="to" id="toSelect" class="form-select me-2" style="max-width:150px; display:none;">
                        <option value="<?php echo htmlspecialchars($u1); ?>"><?php echo htmlspecialchars($u1); ?></option>
                        <option value="<?php echo htmlspecialchars($u2); ?>"><?php echo htmlspecialchars($u2); ?></option>
                    </select>
                    <input type="text" name="message" class="form-control me-2" required>
                    <button class="btn btn-primary">Gönder</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="profiles" role="tabpanel">
                <table class="table table-sm">
                    <tr><th>Kullanıcı ID</th><th>Ad Soyad</th><th>Birim</th><th>Telefon</th><th>Doğum</th><th></th></tr>
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
                                <td><button class="btn btn-sm btn-secondary">Kaydet</button></td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </table>
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
                    <button class="btn btn-primary">Kaydet</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/theme.js"></script>
    <script>
        const hash = location.hash;
        if(hash){
            const trigger = document.querySelector(`#adminTab button[data-bs-target="${hash}"]`);
            if(trigger){
                new bootstrap.Tab(trigger).show();
            }
        }
        document.querySelectorAll('#adminTab button[data-bs-toggle="tab"]').forEach(btn=>{
            btn.addEventListener('shown.bs.tab', e=>{
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
