<?php
$file = __DIR__ . '/../data/trainings.json';
$trainings = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $trainings = json_decode($json, true) ?: [];
}
?>
<h2 class="mb-3">Eğitimler</h2>
<ul class="list-group">
<?php foreach ($trainings as $t): ?>
    <li class="list-group-item"><strong><?php echo $t['title']; ?></strong> - <?php echo $t['description']; ?></li>
<?php endforeach; ?>
</ul>
