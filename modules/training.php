<?php
$trainings = [
    ["title" => "Hijyen Eğitimi", "description" => "Temel hijyen kuralları"],
    ["title" => "Acil Müdahale", "description" => "Acil durumlarda yapılacaklar"],
];
?>
<h2 class="mb-3">Eğitimler</h2>
<ul class="list-group">
<?php foreach ($trainings as $t): ?>
    <li class="list-group-item">
        <strong><?php echo $t['title']; ?></strong> - <?php echo $t['description']; ?>
    </li>
<?php endforeach; ?>
</ul>
