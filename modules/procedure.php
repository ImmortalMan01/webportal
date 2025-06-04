<?php
require_once __DIR__ . '/../db.php';
$stmt = $pdo->query('SELECT name, file FROM procedures ORDER BY name');
$procedures = $stmt->fetchAll();
?>
<h2 class="mb-3">Prosedürler</h2>
<ul class="list-group">
<?php foreach ($procedures as $p): ?>
    <li class="list-group-item"><a href="docs/<?php echo $p['file']; ?>" target="_blank"><?php echo $p['name']; ?></a></li>
<?php endforeach; ?>
</ul>
