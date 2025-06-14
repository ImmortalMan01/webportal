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
    $pdo->exec("CREATE TABLE IF NOT EXISTS announcement_views (user_id INT PRIMARY KEY, last_seen DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00', FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)");
    $newAnnCount = 0;
    $newAnnouncements = [];
    if(isset($_SESSION['user'])){
        $uidStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $uidStmt->execute([$user]);
        $uid = $uidStmt->fetchColumn();
        if($uid){
            $lsStmt = $pdo->prepare('SELECT last_seen FROM announcement_views WHERE user_id = ?');
            $lsStmt->execute([$uid]);
            $lastSeen = $lsStmt->fetchColumn() ?: '1970-01-01 00:00:00';
            $cnt = $pdo->prepare('SELECT COUNT(*) FROM announcements WHERE publish_date > ?');
            $cnt->execute([$lastSeen]);
            $newAnnCount = (int)$cnt->fetchColumn();
            if($newAnnCount > 0){
                $det = $pdo->prepare('SELECT content, publish_date FROM announcements WHERE publish_date > ? ORDER BY publish_date DESC');
                $det->execute([$lastSeen]);
                $newAnnouncements = $det->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
    $notifCount = $newAnnCount;
?>
<nav class="portal-nav" aria-label="Üst Menü">
<script>document.body.classList.add('home-dashboard');</script>
  <div class="nav-left">
    <div class="portal-logo"><?php echo htmlspecialchars($site_name); ?></div>
    <div class="welcome">Hoşgeldiniz, <?php echo htmlspecialchars($full); ?></div>
    <span class="role-pill"><?php echo htmlspecialchars($role); ?></span>
  </div>
  <div class="nav-actions">
    <div class="drop-down" id="notifMenu">
      <button id="notifBtn" class="icon-btn drop-down__button" aria-label="Bildirimler">
        <span class="material-icons">notifications</span>
        <?php if($notifCount>0): ?><span class="badge"><?php echo $notifCount; ?></span><?php endif; ?>
      </button>
      <div class="drop-down__menu-box">
        <ul class="drop-down__menu">
          <?php if($newAnnCount>0 && !empty($newAnnouncements)): ?>
            <?php foreach($newAnnouncements as $ann): ?>
              <li class="drop-down__item"><a href="#" class="newAnnLink notif-text" data-content="<?php echo htmlspecialchars($ann['content'], ENT_QUOTES); ?>" data-date="<?php echo $ann['publish_date']; ?>"><?php echo htmlspecialchars($ann['content']); ?></a></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="drop-down__item">Bildirim yok</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <button id="themeToggleGlobal" class="icon-btn" aria-label="Tema">🌙</button>
    <div class="drop-down avatar">
      <button class="drop-down__button icon-btn" aria-label="Kullanıcı">
        <?php echo strtoupper(mb_substr($full,0,1)); ?>
      </button>
      <div class="drop-down__menu-box">
        <ul class="drop-down__menu">
          <li class="drop-down__item"><a href="pages/profile.php">Profil</a></li>
          <li class="drop-down__item"><a href="pages/logout.php">Çıkış</a></li>
        </ul>
      </div>
    </div>
    <div class="drop-down" id="desktopMenu">
      <button id="mobileMenuBtn" class="icon-btn drop-down__button" aria-label="Ayarlar">
        <span class="material-icons">menu</span>
      </button>
      <div class="drop-down__menu-box">
        <ul class="drop-down__menu">
          <li class="drop-down__item"><a href="pages/profile.php"><span class="material-icons drop-down__item-icon">person</span><span class="drop-down__item-text">Profil</span></a></li>
          <li class="drop-down__item"><a href="pages/messages.php"><span class="material-icons drop-down__item-icon">mail</span><span class="drop-down__item-text">Mesajlar</span></a></li>
          <?php if($role === 'admin'): ?>
          <li class="drop-down__item"><a href="pages/admin.php"><span class="material-icons drop-down__item-icon">admin_panel_settings</span><span class="drop-down__item-text">Admin Paneli</span></a></li>
          <?php endif; ?>
          <li class="drop-down__item"><a href="pages/logout.php"><span class="material-icons drop-down__item-icon">logout</span><span class="drop-down__item-text">Çıkış</span></a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<div id="mobileMenu" class="mobile-menu" aria-label="Mobil Menü">
  <button id="closeMenu" class="close-btn" aria-label="Kapat">✕</button>
  <div class="menu-welcome">Hoşgeldiniz, <?php echo htmlspecialchars($full); ?></div>
  <ul class="menu-list">
    <li><a href="pages/profile.php"><span class="material-icons">person</span> Profil</a></li>
    <li><a href="pages/messages.php"><span class="material-icons">mail</span> Mesajlar</a></li>
    <?php if($role === 'admin'): ?>
    <li><a href="pages/admin.php"><span class="material-icons">admin_panel_settings</span> Admin Paneli</a></li>
    <?php endif; ?>
    <li><a href="pages/logout.php"><span class="material-icons">logout</span> Çıkış</a></li>
  </ul>
</div>
<div class="dashboard">
  <?php
    $modCols = $pdo->query("SHOW COLUMNS FROM modules")->fetchAll(PDO::FETCH_COLUMN);
    if(!in_array('icon',$modCols)){
        $pdo->exec("ALTER TABLE modules ADD COLUMN icon VARCHAR(50) DEFAULT ''");
    }
    if(!in_array('description',$modCols)){
        $pdo->exec("ALTER TABLE modules ADD COLUMN description VARCHAR(255) DEFAULT ''");
    }
    if(!in_array('color',$modCols)){
        $pdo->exec("ALTER TABLE modules ADD COLUMN color VARCHAR(20) DEFAULT '#3fa7ff'");
    }
    if(!in_array('badge',$modCols)){
        $pdo->exec("ALTER TABLE modules ADD COLUMN badge VARCHAR(50) DEFAULT ''");
    }
    if(!in_array('badge_class',$modCols)){
        $pdo->exec("ALTER TABLE modules ADD COLUMN badge_class VARCHAR(20) DEFAULT 'badge-blue'");
    }
    if(!in_array('enabled',$modCols)){
        $pdo->exec("ALTER TABLE modules ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1");
    }
    $moduleRows = $pdo->query('SELECT name,file,icon,description,color,badge,badge_class FROM modules WHERE enabled=1 ORDER BY id')->fetchAll();
  ?>
  <div class="dashboard-grid">
    <?php foreach($moduleRows as $m): ?>
      <div class="dashboard-card" data-file="<?php echo htmlspecialchars($m['file']); ?>">
        <i class="<?php echo htmlspecialchars($m['icon']); ?>" style="color:<?php echo htmlspecialchars($m['color'] ?: '#3fa7ff'); ?>;font-size:48px;"></i>
        <h3><?php echo htmlspecialchars($m['name']); ?></h3>
        <?php if(!empty($m['description'])): ?>
        <p><?php echo htmlspecialchars($m['description']); ?></p>
        <?php endif; ?>
        <?php if(!empty($m['badge'])): ?>
        <span class="status-badge <?php echo htmlspecialchars($m['badge_class']); ?>"><?php echo htmlspecialchars($m['badge']); ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <section class="announcements">
    <h3>Duyurular ve Bilgilendirmeler</h3>
    <ul>
      <?php foreach($announcements as $a): ?>
        <li class="announcement-item" data-content="<?php echo htmlspecialchars($a['content'], ENT_QUOTES); ?>" data-date="<?php echo $a['publish_date']; ?>">
          <span class="title"><?php echo htmlspecialchars($a['content']); ?></span>
          <div class="date"><?php echo $a['publish_date']; ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Duyuru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.dashboard-card').forEach(function(el){
      el.addEventListener('click', function(){
        var file = el.getAttribute('data-file');
        if(file){
          window.location.href = 'index.php?module=' + file;
        }
      });
    });
    var annModalEl = document.getElementById('announcementModal');
    if (annModalEl) {
      var annModal = new bootstrap.Modal(annModalEl);
      document.querySelectorAll('.announcement-item').forEach(function(item){
        item.addEventListener('click',function(){
          document.querySelector('#announcementModal .modal-body').innerText = this.getAttribute('data-content');
          var date = this.getAttribute('data-date');
          document.querySelector('#announcementModal .modal-title').innerText = 'Duyuru - ' + date;
          annModal.show();
        });
      });
      document.querySelectorAll('.newAnnLink').forEach(function(newAnnLink){
        newAnnLink.addEventListener('click',function(e){
          e.preventDefault();
          document.querySelector('#announcementModal .modal-body').innerText = this.getAttribute('data-content');
          var date = this.getAttribute('data-date');
          document.querySelector('#announcementModal .modal-title').innerText = 'Duyuru - ' + date;
          annModal.show();
          fetch('pages/mark_announcement.php', {method:'POST'});
          var badge = document.querySelector('#notifBtn .badge');
          if(badge) badge.remove();
          var menu = document.querySelector('#notifMenu .drop-down__menu');
          if(menu) menu.innerHTML = '<li class="drop-down__item">Bildirim yok</li>';
        });
      });
    }
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
