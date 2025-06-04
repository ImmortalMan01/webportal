<?php
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
function render_menu() {
    $items = [
        'shift' => 'Vardiya Sistemi',
        'training' => 'Eğitimler',
        'exam' => 'Sınavlar',
        'procedure' => 'Prosedürler'
    ];
    foreach ($items as $key => $label) {
        echo "<li class='nav-item'><a class='nav-link' href='?module=$key'>$label</a></li>";
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Sağlık Personeli Portalı</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sağlık Personeli Portalı</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php render_menu(); ?>
      </ul>
    </div>
  </div>
</nav>
    <div class="container py-4">
        <?php
        switch ($module) {
            case 'shift':
                include 'modules/shift.php';
                break;
            case 'training':
                include 'modules/training.php';
                break;
            case 'exam':
                include 'modules/exam.php';
                break;
            case 'procedure':
                include 'modules/procedure.php';
                break;
            default:
                echo "<p>Hoş geldiniz! Modüllerden birini seçiniz.</p>";
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
