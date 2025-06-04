<?php
require_once __DIR__ . '/../includes/db.php';
$stmt = $pdo->query('SELECT title, description FROM trainings ORDER BY id');
$trainings = $stmt->fetchAll();
?>
<h2 class="mb-3">Eğitimler</h2>
<ul class="list-group">
<?php foreach ($trainings as $t): ?>
    <li class="list-group-item"><strong><?php echo $t['title']; ?></strong> - <?php echo $t['description']; ?></li>
<?php endforeach; ?>
</ul>
