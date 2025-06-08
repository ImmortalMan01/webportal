<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo 'Erişim reddedildi';
    exit;
}
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
update_activity($pdo);

$pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    details TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$user = trim($_GET['user'] ?? '');
$action = trim($_GET['action'] ?? '');
$where = [];
$params = [];
if ($user !== '') {
    $where[] = 'u.username = ?';
    $params[] = $user;
}
if ($action !== '') {
    $where[] = 'l.action LIKE ?';
    $params[] = $action;
}
$sql = "SELECT l.id, u.username, l.action, l.timestamp, l.details FROM activity_log l JOIN users u ON l.user_id=u.id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY l.timestamp DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-3">Activity Log</h2>
    <form method="get" class="row g-2 mb-3">
        <div class="col-12 col-md-3"><input type="text" name="user" class="form-control" placeholder="Kullanıcı" value="<?php echo htmlspecialchars($user); ?>"></div>
        <div class="col-12 col-md-3"><input type="text" name="action" class="form-control" placeholder="İşlem" value="<?php echo htmlspecialchars($action); ?>"></div>
        <div class="col-12 col-md-2"><button class="btn btn-primary">Filtrele</button></div>
    </form>
    <table class="table table-sm table-striped">
        <tr><th>ID</th><th>Kullanıcı</th><th>İşlem</th><th>Zaman</th><th>Ayrıntı</th></tr>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo $log['id']; ?></td>
                <td><?php echo htmlspecialchars($log['username']); ?></td>
                <td><?php echo htmlspecialchars($log['action']); ?></td>
                <td><?php echo $log['timestamp']; ?></td>
                <td><?php echo htmlspecialchars($log['details']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="admin.php" class="btn btn-secondary">Geri</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/theme.js"></script>
</body>
</html>
