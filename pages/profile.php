<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
update_activity($pdo);
$message = '';
$passMessage = '';
$username = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
$userId = $stmt->fetchColumn();
if (!$userId) {
    die('Kullanıcı bulunamadı');
}
$stmt = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
$stmt->execute([$userId]);
$unreadCount = $stmt->fetchColumn();
$upload = '';
$stmt = $pdo->prepare('SELECT full_name, department, phone, birthdate, picture FROM profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['full_name' => '', 'department' => '', 'phone' => '', 'birthdate' => '', 'picture' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['change_password'])){
        $current = $_POST['current_password'] ?? '';
        $new1 = $_POST['new_password'] ?? '';
        $new2 = $_POST['new_password2'] ?? '';
        if($new1 !== $new2){
            $passMessage = 'Yeni şifreler uyuşmuyor';
        }else{
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?');
            $stmt->execute([$userId]);
            $hash = $stmt->fetchColumn();
            if(!password_verify($current,$hash)){
                $passMessage = 'Mevcut şifre yanlış';
            }else{
                $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
                $stmt->execute([password_hash($new1, PASSWORD_DEFAULT), $userId]);
                $passMessage = 'Şifre güncellendi';
            }
        }
    } else {
        $full  = $_POST['full_name'] ?? '';
        $dept  = $_POST['department'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $birth = $_POST['birthdate'] ?? null;
        $pic   = $profile['picture'] ?? '';

    if (!empty($_FILES['picture']['name'])) {
        $dir = 'uploads/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $fname = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $dir . $fname)) {
            $pic = $fname;
        }
    }

    $check = $pdo->prepare('SELECT COUNT(*) FROM profiles WHERE user_id = ?');
    $check->execute([$userId]);
    if ($check->fetchColumn()) {
        $stmt = $pdo->prepare('UPDATE profiles SET full_name = ?, department = ?, phone = ?, birthdate = ?, picture = ? WHERE user_id = ?');
        $stmt->execute([$full, $dept, $phone, $birth, $pic, $userId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO profiles (user_id, full_name, department, phone, birthdate, picture) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $full, $dept, $phone, $birth, $pic]);
    }
        $message = 'Profil güncellendi';
    }
}
$stmt = $pdo->prepare('SELECT full_name, department, phone, birthdate, picture FROM profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['full_name' => '', 'department' => '', 'phone' => '', 'birthdate' => '', 'picture' => ''];
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profilim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-3">Profilim</h2>
    <?php if ($unreadCount) echo "<div class='alert alert-info'>Okunmamış mesajlar: $unreadCount</div>"; ?>
    <?php if ($message) echo "<div class='alert alert-success'>$message</div>"; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($profile['full_name']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Birim</label>
            <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($profile['department']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Telefon</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Doğum Tarihi</label>
            <input type="date" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($profile['birthdate']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Profil Resmi</label>
            <?php if ($profile['picture']) echo "<br><img src='uploads/" . htmlspecialchars($profile['picture']) . "' width='80' class='mb-2'>"; ?>
            <input type="file" name="picture" class="form-control">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Kaydet</button>
            <a href="../index.php" class="btn btn-secondary ms-2">Geri</a>
        </div>
    </form>
    <hr class="my-4">
    <h4>Şifre Değiştir</h4>
    <?php if ($passMessage) echo "<div class='alert alert-info'>$passMessage</div>"; ?>
    <form method="post" class="row g-3">
        <input type="hidden" name="change_password" value="1">
        <div class="col-md-4">
            <label class="form-label">Mevcut Şifre</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Yeni Şifre</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Yeni Şifre (Tekrar)</label>
            <input type="password" name="new_password2" class="form-control" required>
        </div>
        <div class="col-12">
            <button class="btn btn-secondary">Değiştir</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
