<?php
$shifts = [
    ["date" => "2023-05-01", "time" => "08:00 - 16:00"],
    ["date" => "2023-05-02", "time" => "16:00 - 00:00"],
    ["date" => "2023-05-03", "time" => "00:00 - 08:00"],
];
?>
<h2>Vardiya Sistemi</h2>
<table border='1' cellpadding='5'>
    <tr><th>Tarih</th><th>Vardiya</th></tr>
    <?php foreach ($shifts as $s): ?>
        <tr><td><?php echo $s['date']; ?></td><td><?php echo $s['time']; ?></td></tr>
    <?php endforeach; ?>
</table>
