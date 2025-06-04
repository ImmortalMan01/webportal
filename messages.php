<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';
$current = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$current]);
$currentId = $stmt->fetchColumn();
if (!$currentId) die('Kullanıcı bulunamadı');
$targetUser = $_GET['user'] ?? '';
if ($targetUser) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$targetUser]);
    $targetId = $stmt->fetchColumn();
    if (!$targetId) die('Kullanıcı bulunamadı');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $msg = $_POST['message'] ?? '';
        $s = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
        $s->execute([$currentId, $targetId, $msg]);
    }
    $conv = $pdo->prepare('SELECT sender_id, receiver_id, message, created_at FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at');
    $conv->execute([$currentId, $targetId, $targetId, $currentId]);
    $messages = $conv->fetchAll();
}
$allUsers = $pdo->query('SELECT username FROM users WHERE username <> ' . $pdo->quote($current) . ' ORDER BY username')->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesajlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-3">Mesajlar</h2>
    <div class="row">
        <div class="col-md-4">
            <h5>Kullanıcılar</h5>
            <ul class="list-group mb-3">
                <?php foreach ($allUsers as $u): ?>
                    <li class="list-group-item<?php if ($targetUser==$u['username']) echo ' active'; ?>">
                        <a href="messages.php?user=<?php echo urlencode($u['username']); ?>" class="text-decoration-none<?php if ($targetUser==$u['username']) echo ' text-light'; ?>">
                            <?php echo htmlspecialchars($u['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-8">
            <?php if ($targetUser): ?>
                <div class="border p-3 mb-3" style="height:300px; overflow-y:auto;">
                    <?php foreach ($messages as $m): ?>
                        <div class="mb-2">
                            <strong><?php echo $m['sender_id']==$currentId ? 'Ben' : htmlspecialchars($targetUser); ?>:</strong>
                            <?php echo htmlspecialchars($m['message']); ?>
                            <small class="text-muted float-end"><?php echo $m['created_at']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" class="d-flex">
                    <input type="text" name="message" class="form-control me-2" placeholder="Mesaj yaz..." required>
                    <button class="btn btn-primary">Gönder</button>
                </form>
            <?php else: ?>
                <p>Soldaki listeden bir kullanıcı seçiniz.</p>
            <?php endif; ?>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary mt-3">Geri</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
