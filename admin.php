<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $action  = $_POST['action'] ?? '';
    if ($section && $action) {
        if ($section === 'users') {
            if ($action === 'add') {
                $u = $_POST['username'] ?? '';
                $p = $_POST['password'] ?? '';
                $r = $_POST['role'] ?? 'user';
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
        }
        header('Location: admin.php#' . $section);
        exit;
    }
}

$stmt = $pdo->query('SELECT username, role FROM users ORDER BY username');
$users = $stmt->fetchAll();
$shifts = $pdo->query('SELECT id, date, time FROM shifts ORDER BY date')->fetchAll();
$trainings = $pdo->query('SELECT id, title, description FROM trainings ORDER BY id')->fetchAll();
$exams = $pdo->query('SELECT id, title, date FROM exams ORDER BY date')->fetchAll();
$procedures = $pdo->query('SELECT id, name, file FROM procedures ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
                            <option value="user">user</option>
                            <option value="admin">admin</option>
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
                                        <option value="user" <?php if ($info['role']=='user') echo 'selected'; ?>>user</option>
                                        <option value="admin" <?php if ($info['role']=='admin') echo 'selected'; ?>>admin</option>
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
