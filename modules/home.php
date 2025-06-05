<?php
$role = $_SESSION['role'] ?? 'guest';
$user = $_SESSION['user'] ?? 'Ziyaretçi';
$full = $user;
if(isset($_SESSION['user'])){
    $stmt = $pdo->prepare('SELECT profiles.full_name FROM users LEFT JOIN profiles ON profiles.user_id = users.id WHERE users.username = ?');
    $stmt->execute([$user]);
    $fn = $stmt->fetchColumn();
    if($fn){ $full = $fn; }
}
$theme = get_role_theme($pdo, $role);
if($theme === 'dashboard'):
    $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (id INT AUTO_INCREMENT PRIMARY KEY, content TEXT NOT NULL, publish_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)");
    $announcements = $pdo->query('SELECT content, publish_date FROM announcements ORDER BY publish_date DESC')->fetchAll();
?>
<nav class="portal-nav">
<script>document.body.classList.add('home-dashboard');</script>
  <div class="nav-left">
    <div class="portal-logo"><?php echo htmlspecialchars($site_name); ?></div>
    <div class="welcome">Hoşgeldiniz, <?php echo htmlspecialchars($full); ?></div>
    <span class="role-pill"><?php echo htmlspecialchars($role); ?></span>
  </div>
  <div class="nav-right">
    <div class="drop-down">
      <div class="drop-down__button">
        <span class="drop-down__name">Ayarlar</span>
        <i class="fa-solid fa-gear drop-down__icon"></i>
      </div>
      <div class="drop-down__menu-box">
        <ul class="drop-down__menu">
          <li class="drop-down__item"><a href="pages/profile.php"><i class="fa-solid fa-user drop-down__item-icon"></i><span class="drop-down__item-text">Profil</span></a></li>
          <li class="drop-down__item"><a href="pages/messages.php"><i class="fa-solid fa-envelope drop-down__item-icon"></i><span class="drop-down__item-text">Mesajlar</span></a></li>
          <?php if($role === 'admin'): ?>
          <li class="drop-down__item"><a href="pages/admin.php"><i class="fa-solid fa-toolbox drop-down__item-icon"></i><span class="drop-down__item-text">Admin Paneli</span></a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <button id="themeToggleGlobal" aria-label="Tema" role="button">🌙</button>
    <a href="pages/logout.php" class="logout-btn" aria-label="Çıkış" role="button"><i class="fa-solid fa-arrow-right-from-bracket"></i> Çıkış</a>
  </div>
</nav>
<div class="dashboard">
  <div class="dashboard-grid">
    <div class="dashboard-card" id="work-list">
      <i class="fa-solid fa-calendar" style="color:#3fa7ff;font-size:48px;"></i>
      <h3>ÇALIŞMA LİSTESİ</h3>
      <p>Vardiyalarınızı ve mesai planınızı anında görün.</p>
      <span class="status-badge badge-green">Güncel</span>
    </div>
    <div class="dashboard-card" id="procedure-read">
      <i class="fa-solid fa-book" style="color:#0dd4a3;font-size:48px;"></i>
      <h3>PROSEDÜR OKUMA</h3>
      <p>Güncel prosedürlere hızla erişin, bilgilenin.</p>
      <span class="status-badge badge-blue">12 Yeni</span>
    </div>
    <div class="dashboard-card" id="active-exams">
      <i class="fa-solid fa-clipboard-check" style="color:#ff5555;font-size:48px;"></i>
      <h3>AKTİF SINAVLAR</h3>
      <p>Sınavlarınızı takip edin, başarınızı ölçün.</p>
      <span class="status-badge badge-orange">3 Bekleyen</span>
    </div>
    <div class="dashboard-card" id="revised-procedures">
      <i class="fa-solid fa-book" style="color:#3fa7ff;font-size:48px;"></i>
      <h3>REVİZE PROSEDÜRLER</h3>
      <p>En son güncellemeleri kaçırmayın, onaylayın.</p>
      <span class="status-badge badge-blue">5 Yeni</span>
    </div>
    <div class="dashboard-card" id="trainings">
      <i class="fa-solid fa-graduation-cap" style="color:#3fa7ff;font-size:48px;"></i>
      <h3>EĞİTİMLER</h3>
      <p>Kariyerinizi geliştirecek eğitimlere katılın.</p>
      <span class="status-badge badge-blue">8 Aktif</span>
    </div>
    <div class="dashboard-card" id="games">
      <i class="fa-solid fa-gamepad" style="color:#b54bff;font-size:48px;"></i>
      <h3>OYUNLAR</h3>
      <p>Öğrenirken eğlenin, bilginizi test edin.</p>
      <span class="status-badge badge-blue">4 Yeni</span>
    </div>
    <div class="dashboard-card" id="podcast">
      <i class="fa-solid fa-podcast" style="color:#ff9a28;font-size:48px;"></i>
      <h3>PODCAST</h3>
      <p>Sağlık ve liderlikte güncel kalın, dinleyin.</p>
      <span class="status-badge badge-blue">6 Yeni Bölüm</span>
    </div>
    <div class="dashboard-card" id="performance">
      <i class="fa-solid fa-chart-line" style="color:#0dd4a3;font-size:48px;"></i>
      <h3>PERFORMANS</h3>
      <p>Hedeflerinizi ve performansınızı takip edin.</p>
      <span class="status-badge badge-orange">1 Yaklaşıyor</span>
    </div>
  </div>
  <section class="announcements">
    <h3>Duyurular ve Bilgilendirmeler</h3>
    <ul>
      <?php foreach($announcements as $a): ?>
        <li class="announcement-item">
          <span class="title"><?php echo htmlspecialchars($a['content']); ?></span>
          <div class="date"><?php echo $a['publish_date']; ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>
<script>
  document.querySelectorAll('.dashboard-card').forEach(function(el){
    el.addEventListener('click',function(){console.log(el.id);});
  });
</script>
<?php
else:
?>
<h2 class="mb-3">Merhaba <?php echo htmlspecialchars($user); ?></h2>
<?php if(!isset($_SESSION['user'])): ?>
<p>Portal özelliklerine erişmek için lütfen giriş yapın.</p>
<?php elseif($role === 'Sorumlu Hemşire'): ?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Hasta Listesi</div>
            <div class="card-body">
                <p>Görevli olduğunuz hastaların özet bilgileri.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Randevular</div>
            <div class="card-body">
                <p>Yaklaşan randevularınızı görüntüleyin.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Duyurular</div>
            <div class="card-body">
                <p>Sorumlu hemşirelere özel son duyurular.</p>
            </div>
        </div>
    </div>
</div>
<?php elseif($role === 'Klinik Eğitim Hemşiresi'): ?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">İlaç Takip</div>
            <div class="card-body">
                <p>Hastaların ilaç planlarını kontrol edin.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Vardiyalarım</div>
            <div class="card-body">
                <p>Yaklaşan vardiya bilgileri.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Bildirimler</div>
            <div class="card-body">
                <p>Klinik eğitim hemşirelerine özel güncellemeler.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<p>Portal modüllerine menüden erişebilirsiniz.</p>
<?php endif; ?>
<?php endif; ?>
