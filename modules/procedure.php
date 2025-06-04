<?php
$procedures = [
    ["name" => "Sterilizasyon Prosedürü", "file" => "sterilizasyon.pdf"],
    ["name" => "Hasta Taşıma Prosedürü", "file" => "hasta_tasima.pdf"],
];
?>
<h2>Prosedürler</h2>
<ul>
<?php foreach ($procedures as $p): ?>
    <li><a href="docs/<?php echo $p['file']; ?>" target="_blank"><?php echo $p['name']; ?></a></li>
<?php endforeach; ?>
</ul>
