<?php
$trainings = [
    ["title" => "Hijyen Eğitimi", "description" => "Temel hijyen kuralları"],
    ["title" => "Acil Müdahale", "description" => "Acil durumlarda yapılacaklar"],
];
?>
<h2>Eğitimler</h2>
<ul>
<?php foreach ($trainings as $t): ?>
    <li><strong><?php echo $t['title']; ?></strong> - <?php echo $t['description']; ?></li>
<?php endforeach; ?>
</ul>
