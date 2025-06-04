<?php
$file = __DIR__ . '/../data/procedures.json';
$procedures = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $procedures = json_decode($json, true) ?: [];
}
?>
<h2 class="mb-3">Prosedürler</h2>
<ul class="list-group">
<?php foreach ($procedures as $p): ?>
    <li class="list-group-item"><a href="docs/<?php echo $p['file']; ?>" target="_blank"><?php echo $p['name']; ?></a></li>
<?php endforeach; ?>
</ul>
