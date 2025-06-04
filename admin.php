<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}

require 'db.php';

function load_data($name) {
    $file = __DIR__ . "/data/{$name}.json";
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return $data ?: [];
    }
    return [];
}

function save_data($name, $data) {
    $file = __DIR__ . "/data/{$name}.json";
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $action  = $_POST['action'] ?? '';
    $data    = load_data($section);
    if ($section && $action) {
        if ($action === 'add') {
            if ($section === 'shifts') {
                $data[] = ['date' => $_POST['date'], 'time' => $_POST['time']];
            } elseif ($section === 'trainings') {
                $data[] = ['title' => $_POST['title'], 'description' => $_POST['description']];
            } elseif ($section === 'exams') {
                $data[] = ['title' => $_POST['title'], 'date' => $_POST['date']];
            } elseif ($section === 'procedures') {
                $data[] = ['name' => $_POST['name'], 'file' => $_POST['file']];
            }
        } elseif ($action === 'delete') {
            $idx = (int)$_POST['index'];
            if (isset($data[$idx])) {
                array_splice($data, $idx, 1);
            }
        }
        save_data($section, $data);
        header('Location: admin.php#' . $section);
        exit;
    }
}

$stmt = $pdo->query('SELECT username, role FROM users ORDER BY username');
$users = $stmt->fetchAll();
$shifts = load_data('shifts');
$trainings = load_data('trainings');
$exams = load_data('exams');
$procedures = load_data('procedures');
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
                <ul class="list-group mb-3">
                    <?php foreach ($users as $info): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($info['username']) . ' (' . $info['role'] . ')'; ?></li>
                    <?php endforeach; ?>
                </ul>
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
                    <?php foreach ($shifts as $i => $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['date']); ?></td>
                            <td><?php echo htmlspecialchars($s['time']); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="section" value="shifts">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="index" value="<?php echo $i; ?>">
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
                    <?php foreach ($trainings as $i => $t): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong><?php echo htmlspecialchars($t['title']); ?></strong> - <?php echo htmlspecialchars($t['description']); ?></span>
                            <form method="post" class="ms-3">
                                <input type="hidden" name="section" value="trainings">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="index" value="<?php echo $i; ?>">
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
                    <?php foreach ($exams as $i => $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['title']); ?></td>
                            <td><?php echo htmlspecialchars($e['date']); ?></td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="section" value="exams">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="index" value="<?php echo $i; ?>">
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
                    <?php foreach ($procedures as $i => $p): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="docs/<?php echo htmlspecialchars($p['file']); ?>" target="_blank"><?php echo htmlspecialchars($p['name']); ?></a>
                            <form method="post" class="ms-3">
                                <input type="hidden" name="section" value="procedures">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="index" value="<?php echo $i; ?>">
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
