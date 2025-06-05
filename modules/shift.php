<?php
require_once __DIR__ . '/../includes/db.php';
$stmt = $pdo->query('SELECT date, time FROM shifts ORDER BY date');
$shifts = $stmt->fetchAll();
?>
<h2 class="mb-3">Çalışma Listesi</h2>
<table class="table table-striped">
    <tr><th>Tarih</th><th>Vardiya</th></tr>
    <?php foreach ($shifts as $s): ?>
        <tr><td><?php echo $s['date']; ?></td><td><?php echo $s['time']; ?></td></tr>
    <?php endforeach; ?>
</table>
