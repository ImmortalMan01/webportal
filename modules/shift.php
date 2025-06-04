<?php
$file = __DIR__ . '/../data/shifts.json';
$shifts = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $shifts = json_decode($json, true) ?: [];
}
?>
<h2 class="mb-3">Vardiya Sistemi</h2>
<table class="table table-striped">
    <tr><th>Tarih</th><th>Vardiya</th></tr>
    <?php foreach ($shifts as $s): ?>
        <tr><td><?php echo $s['date']; ?></td><td><?php echo $s['time']; ?></td></tr>
    <?php endforeach; ?>
</table>
