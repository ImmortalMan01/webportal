<?php
// Display the site name configured in the admin panel if available.
// Fallback to the server hostname when the setting is not defined.
$server = $site_name ?? ($_SERVER['SERVER_NAME'] ?? 'SUNUCU');

// Uppercase function aware of Turkish characters
function tr_upper(string $text): string {
    $map = [
        'i' => 'İ', 'ı' => 'I', 'ğ' => 'Ğ',
        'ü' => 'Ü', 'ş' => 'Ş', 'ö' => 'Ö', 'ç' => 'Ç'
    ];
    return strtoupper(strtr($text, $map));
}
?>
<script>document.body.classList.add('worklist-page');</script>
<link rel="stylesheet" href="assets/worklist.css">
<link rel="stylesheet" href="assets/holiday-calendar.css">
<div id="wls-app">
  <header class="wl-header">
    <div class="left">
      <button id="wlSidebarToggle" class="menu-toggle" type="button"><i class="fa-solid fa-bars"></i></button>
      <a href="index.php" class="home-link">Ana Sayfa</a>
    </div>
  </header>
  <div class="wl-container">
    <aside class="wl-sidebar">
      <ul>
        <li data-view="weekly" class="active"><i class="fa-solid fa-calendar-week"></i><span>Haftalık Listem</span></li>
        <li data-view="calendar"><i class="fa-solid fa-calendar-days"></i><span>İstek/İzin Takvimi</span></li>
        <li data-view="yearly"><i class="fa-solid fa-calendar"></i><span>Yıllık İzin</span></li>
        <li data-view="requests"><i class="fa-solid fa-list"></i><span>İsteklerim</span></li>
        <li data-view="stats"><i class="fa-solid fa-chart-column"></i><span>Vardiya İstatistikleri</span></li>
      </ul>
      <div class="sidebar-footer"><?php echo htmlspecialchars($server); ?></div>
    </aside>
    <main class="wl-main">
      <div id="weekly" class="wl-view active"><div class="wl-card">Haftalık Liste Henüz Uygulanmadı.</div></div>
      <div id="calendar" class="wl-view"><div class="wl-card"><div id="calendarComponent" class="wl-calendar"></div></div></div>
      <div id="yearly" class="wl-view"><div class="wl-card">Yıllık İzin Görünümü.</div></div>
      <div id="requests" class="wl-view"><div class="wl-card">İstekler panelinden görüntüleyiniz.</div></div>
      <div id="stats" class="wl-view"><div class="wl-card">İstatistikler.</div></div>
    </main>
  </div>
</div>
<div id="wls-modal">
  <div class="wl-modal-card">
    <h3>İstek Oluştur/Düzenle</h3>
    <div class="subtitle"></div>
    <div class="radios">
      <label><input type="radio" name="type" value="gündüz"> GÜNDÜZ</label><br>
      <label><input type="radio" name="type" value="nöbet"> NÖBET</label><br>
      <label><input type="radio" name="type" value="izin"> İZİN</label><br>
      <label><input type="radio" name="type" value="diğer"> DİĞER</label>
    </div>
    <div class="actions">
      <button class="ghost cancel">İptal</button>
      <button class="fill save">Kaydet</button>
    </div>
  </div>
</div>
<div id="wls-drawer">
  <div class="header"><span>İsteklerim</span><i class="fa-solid fa-xmark close" style="cursor:pointer"></i></div>
  <div class="list"></div>
</div>
<script src="assets/holiday-calendar.js"></script>
<script src="assets/worklist.js"></script>
<script src="assets/worklist-sidebar.js"></script>
