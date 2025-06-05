<?php
$role = $_SESSION['role'] ?? 'guest';
$user = $_SESSION['user'] ?? 'Ziyaretçi';
?>
<h2 class="mb-3">Merhaba <?php echo htmlspecialchars($user); ?></h2>
<?php if(!isset($_SESSION['user'])): ?>
<p>Portal özelliklerine erişmek için lütfen giriş yapın.</p>
<?php else: ?>
<?php if($role === 'Sorumlu Hemşire'): ?>
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
<?php elseif($role === 'Normal Personel'): ?>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Randevu Yönetimi</div>
            <div class="card-body">
                <p>Günlük randevu kayıtlarını düzenleyin.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Hasta Karşılama</div>
            <div class="card-body">
                <p>Yeni gelen hastaları karşılamak için notlar.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Duyurular</div>
            <div class="card-body">
                <p>Personellere yönelik bilgilendirmeler.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<p>Portal modüllerine menüden erişebilirsiniz.</p>
<?php endif; ?>
<?php endif; ?>
