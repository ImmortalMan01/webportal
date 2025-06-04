<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';
$user = $_GET['user'] ?? '';
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$user]);
$userId = $stmt->fetchColumn();
if (!$userId) {
    die('Kullanıcı bulunamadı');
}
$stmt = $pdo->prepare('SELECT full_name, department, phone, birthdate, picture FROM profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['full_name'=>'','department'=>'','phone'=>'','birthdate'=>'','picture'=>''];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-3">Profil: <?php echo htmlspecialchars($user); ?></h2>
    <?php if ($profile['picture']) echo "<img src='uploads/".htmlspecialchars($profile['picture'])."' class='mb-3' width='120'>"; ?>
    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($profile['full_name']); ?></li>
        <li class="list-group-item"><strong>Birim:</strong> <?php echo htmlspecialchars($profile['department']); ?></li>
        <li class="list-group-item"><strong>Telefon:</strong> <?php echo htmlspecialchars($profile['phone']); ?></li>
        <li class="list-group-item"><strong>Doğum Tarihi:</strong> <?php echo htmlspecialchars($profile['birthdate']); ?></li>
    </ul>
    <a href="messages.php?user=<?php echo urlencode($user); ?>" class="btn btn-primary">Mesaj Gönder</a>
    <a href="users.php" class="btn btn-secondary ms-2">Geri</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
