<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
update_activity($pdo);
$users = $pdo->query('SELECT username FROM users ORDER BY username')->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kullanıcılar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-3">Kullanıcılar</h2>
    <ul class="list-group">
        <?php foreach ($users as $u): ?>
            <li class="list-group-item">
                <a href="view_profile.php?user=<?php echo urlencode($u['username']); ?>">
                    <?php echo htmlspecialchars($u['username']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <a href="../index.php" class="btn btn-secondary mt-3">Geri</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
