<?php
require_once __DIR__ . '/../includes/db.php';
$stmt = $pdo->query('SELECT title, date FROM exams ORDER BY date');
$exams = $stmt->fetchAll();
?>
<h2 class="mb-3">Sınavlar</h2>
<table class="table table-striped">
    <tr><th>Sınav</th><th>Tarih</th></tr>
    <?php foreach ($exams as $e): ?>
        <tr><td><?php echo $e['title']; ?></td><td><?php echo $e['date']; ?></td></tr>
    <?php endforeach; ?>
</table>
