<?php
$exams = [
    ["title" => "Hijyen Sınavı", "date" => "2023-06-01"],
    ["title" => "Acil Müdahale Sınavı", "date" => "2023-06-15"],
];
?>
<h2 class="mb-3">Sınavlar</h2>
<table class="table table-striped">
    <tr><th>Sınav</th><th>Tarih</th></tr>
    <?php foreach ($exams as $e): ?>
        <tr><td><?php echo $e['title']; ?></td><td><?php echo $e['date']; ?></td></tr>
    <?php endforeach; ?>
</table>
