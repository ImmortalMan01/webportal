<?php
$shifts = [
    ["date" => "2023-05-01", "time" => "08:00 - 16:00"],
    ["date" => "2023-05-02", "time" => "16:00 - 00:00"],
    ["date" => "2023-05-03", "time" => "00:00 - 08:00"],
];
?>
<h2 class="mb-3">Vardiya Sistemi</h2>
<table class="table table-bordered">
    <thead class="table-light">
        <tr><th>Tarih</th><th>Vardiya</th></tr>
    </thead>
    <tbody>
    <?php foreach ($shifts as $s): ?>
        <tr>
            <td><?php echo $s['date']; ?></td>
            <td><?php echo $s['time']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
