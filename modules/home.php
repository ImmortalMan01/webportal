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
<script>document.body.classList.add('home-dashboard');</script>
<nav class="dash-header" aria-label="Üst Menü">
  <div class="site-info">
    <span class="site-name"><?php echo htmlspecialchars($site_name); ?></span>
    <span class="welcome">Hoşgeldiniz, <?php echo htmlspecialchars($full); ?></span>
  </div>
  <div class="quick-actions">
    <button class="icon-btn" id="settingsBtn" aria-label="Ayarlar"><span class="material-icons">settings</span></button>
    <button class="icon-btn" id="notifBtn" aria-label="Bildirimler"><span class="material-icons">notifications</span><span class="badge" id="notifBadge" hidden></span></button>
    <button class="icon-btn" id="themeToggleGlobal" aria-label="Tema">🌙</button>
    <div class="drop-down">
      <button class="avatar-btn drop-down__button" aria-label="Kullanıcı">
        <span class="avatar-initial"><?php echo mb_strtoupper(mb_substr($full,0,1)); ?></span>
      </button>
      <div class="drop-down__menu-box">
        <ul class="drop-down__menu">
          <li class="drop-down__item"><a href="pages/profile.php"><span class="material-icons drop-down__item-icon">person</span><span class="drop-down__item-text">Profil</span></a></li>
          <li class="drop-down__item"><a href="pages/logout.php"><span class="material-icons drop-down__item-icon">logout</span><span class="drop-down__item-text">Çıkış</span></a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<div class="settings-menu" id="settingsMenu" aria-hidden="true">
  <div class="menu-header">
    <span class="menu-title"><?php echo htmlspecialchars($site_name); ?></span>
    <button class="close-btn" aria-label="Kapat">✕</button>
  </div>
  <p class="menu-welcome">Hoşgeldiniz, <?php echo htmlspecialchars($full); ?></p>
  <ul class="menu-list">
    <li><a href="pages/profile.php"><span class="material-icons">person</span>Profil</a></li>
    <li><a href="pages/messages.php"><span class="material-icons">mail</span>Mesajlar</a></li>
    <li><a href="pages/worklist.php"><span class="material-icons">checklist</span>Çalışma Listesi</a></li>
    <li><a href="pages/training.php"><span class="material-icons">school</span>Eğitimler</a></li>
    <?php if($role === 'admin'): ?>
    <li><a href="pages/admin.php"><span class="material-icons">admin_panel_settings</span>Admin Paneli</a></li>
    <?php endif; ?>
    <li><a href="pages/logout.php"><span class="material-icons">logout</span>Çıkış</a></li>
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
