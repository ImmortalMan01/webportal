<?php
$file = __DIR__ . '/../data/exams.json';
$exams = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $exams = json_decode($json, true) ?: [];
}
?>
<h2 class="mb-3">Sınavlar</h2>
<table class="table table-striped">
    <tr><th>Sınav</th><th>Tarih</th></tr>
    <?php foreach ($exams as $e): ?>
        <tr><td><?php echo $e['title']; ?></td><td><?php echo $e['date']; ?></td></tr>
    <?php endforeach; ?>
</table>
