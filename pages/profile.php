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
$stmt = $pdo->prepare('SELECT id, email FROM users WHERE username = ?');
$stmt->execute([$username]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $userRow['id'] ?? null;
$email = $userRow['email'] ?? '';
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
$expStmt = $pdo->prepare('SELECT id, title, exp_date FROM experiences WHERE user_id=? ORDER BY exp_date DESC');
$expStmt->execute([$userId]);
$experiences = $expStmt->fetchAll();
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
                log_activity($pdo, 'profile_password_change');
            }
        }
    } elseif(isset($_POST['add_experience'])){
        $title = trim($_POST['exp_title'] ?? '');
        $date  = $_POST['exp_date'] ?? null;
        if($title !== ''){
            $stmt = $pdo->prepare('INSERT INTO experiences (user_id, title, exp_date) VALUES (?,?,?)');
            $stmt->execute([$userId,$title,$date]);
            $message = 'Deneyim eklendi';
            log_activity($pdo, 'profile_add_experience', $title);
        }
    } elseif(isset($_POST['delete_experience'])){
        $id = (int)$_POST['delete_experience'];
        $stmt = $pdo->prepare('DELETE FROM experiences WHERE id=? AND user_id=?');
        $stmt->execute([$id,$userId]);
        $message = 'Deneyim silindi';
        log_activity($pdo, 'profile_delete_experience', (string)$id);
    } else {
        $full   = $_POST['full_name'] ?? '';
        $dept   = $_POST['department'] ?? '';
        $phone  = $_POST['phone'] ?? '';
        $birth  = $_POST['birthdate'] ?? null;
        $newEmail = $_POST['email'] ?? $email;
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
    $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email=? AND id!=?');
    $dup->execute([$newEmail, $userId]);
    if($dup->fetchColumn()==0){
        $pdo->prepare('UPDATE users SET email=? WHERE id=?')->execute([$newEmail, $userId]);
        $email = $newEmail;
    } else {
        $message = 'E-posta zaten kullanılıyor';
    }
        $message = 'Profil güncellendi';
        log_activity($pdo, 'profile_update');
    }
}
$stmt = $pdo->prepare('SELECT full_name, department, phone, birthdate, picture FROM profiles WHERE user_id = ?');
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['full_name' => '', 'department' => '', 'phone' => '', 'birthdate' => '', 'picture' => ''];
$expStmt->execute([$userId]);
$experiences = $expStmt->fetchAll();
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
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Doğum Tarihi</label>
            <input type="date" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($profile['birthdate']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Profil Resmi</label>
            <br><img src="<?php echo $profile['picture'] ? 'uploads/' . htmlspecialchars($profile['picture']) : '../assets/profil.png'; ?>" width="80" class="mb-2">
            <input type="file" name="picture" class="form-control">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Kaydet</button>
            <a href="../index.php" class="btn btn-secondary ms-2">Geri</a>
        </div>
    </form>
    <hr class="my-4">
    <h4>Deneyimler</h4>
    <form method="post" class="row g-3 mb-3">
        <input type="hidden" name="add_experience" value="1">
        <div class="col-md-6"><input type="text" name="exp_title" class="form-control" placeholder="Deneyim" required></div>
        <div class="col-md-4"><input type="date" name="exp_date" class="form-control"></div>
        <div class="col-md-2"><button class="btn btn-secondary w-100">Ekle</button></div>
    </form>
    <table class="table table-sm">
        <tr><th>Deneyim</th><th>Tarih</th><th></th></tr>
        <?php foreach($experiences as $e): ?>
            <tr>
                <td><?php echo htmlspecialchars($e['title']); ?></td>
                <td><?php echo htmlspecialchars($e['exp_date']); ?></td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="delete_experience" value="<?php echo $e['id']; ?>">
                        <button class="btn btn-sm btn-danger">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
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
<script src="../assets/theme.js"></script>
</body>
</html>
