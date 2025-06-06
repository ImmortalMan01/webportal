<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/activity.php';
update_activity($pdo);
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
$expStmt = $pdo->prepare('SELECT title, exp_date FROM experiences WHERE user_id=? ORDER BY exp_date DESC');
$expStmt->execute([$userId]);
$experiences = $expStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/profile-view.css">
</head>
<body>
<?php
    $avatar = $profile['picture'] ? '../uploads/' . $profile['picture'] : '../assets/profil.png';
    $cover = $profile['picture'] ? $avatar : 'https://images.unsplash.com/photo-1549068106-b024baf5062d?auto=format&fit=crop&w=934&q=80';
?>
<div class="card" data-state="#about">
  <div class="card-header">
    <div class="card-cover" style="background-image: url('<?php echo $cover; ?>')"></div>
    <img class="card-avatar" src="<?php echo $avatar; ?>" alt="avatar">
    <h1 class="card-fullname"><?php echo htmlspecialchars($profile['full_name'] ?: $user); ?></h1>
    <h2 class="card-jobtitle"><?php echo htmlspecialchars($profile['department']); ?></h2>
  </div>
  <div class="card-main">
    <div class="card-section is-active" id="about">
      <div class="card-content">
        <div class="card-subtitle">HAKKINDA</div>
        <p class="card-desc">
            Telefon: <?php echo htmlspecialchars($profile['phone']); ?><br>
            Doğum Tarihi: <?php echo htmlspecialchars($profile['birthdate']); ?>
        </p>
      </div>
    </div>
    <div class="card-section" id="experience">
      <div class="card-content">
        <div class="card-subtitle">DENEYİM</div>
        <div class="card-timeline">
          <?php if($experiences): foreach($experiences as $e): ?>
          <?php $year = $e['exp_date'] ? date('Y', strtotime($e['exp_date'])) : '-'; ?>
          <?php $fulldate = $e['exp_date'] ? date('d.m.Y', strtotime($e['exp_date'])) : '-'; ?>
          <div class="card-item" data-year="<?php echo htmlspecialchars($year); ?>">
            <div class="card-item-title">
              <?php echo htmlspecialchars($e['title']); ?>
              <span class="card-item-date"><?php echo htmlspecialchars($fulldate); ?></span>
            </div>
          </div>
          <?php endforeach; else: ?>
          <div class="card-item" data-year="-">
            <div class="card-item-title">Bilgi bulunamadı</div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="card-section" id="contact">
      <div class="card-content">
        <div class="card-subtitle">İLETİŞİM</div>
        <div class="card-contact-wrapper">
          <div class="card-contact">
            <svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
            <?php echo htmlspecialchars($profile['phone']); ?>
          </div>
          <button class="contact-me" onclick="location.href='messages.php?user=<?php echo urlencode($user); ?>'">Mesaj Gönder</button>
        </div>
      </div>
    </div>
    <div class="card-buttons">
      <button data-section="#about" class="is-active">HAKKINDA</button>
      <button data-section="#experience">DENEYİM</button>
      <button data-section="#contact">İLETİŞİM</button>
    </div>
  </div>
</div>
<script src="../assets/profile-view.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/theme.js"></script>
</body>
</html>
