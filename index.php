<?php
session_start();
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
$protected = ['shift', 'training', 'exam', 'procedure'];
if (in_array($module, $protected) && !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
function render_menu() {
    echo "<li class='nav-item'><a class='nav-link' href='?module=shift'>Vardiya Sistemi</a></li>";
    echo "<li class='nav-item'><a class='nav-link' href='?module=training'>Eğitimler</a></li>";
    echo "<li class='nav-item'><a class='nav-link' href='?module=exam'>Sınavlar</a></li>";
    echo "<li class='nav-item'><a class='nav-link' href='?module=procedure'>Prosedürler</a></li>";
}

function render_auth() {
    if (isset($_SESSION['user'])) {
        echo "<span class='navbar-text me-2'>Merhaba " . htmlspecialchars($_SESSION['user']) . "</span>";
        echo "<a class='btn btn-light btn-sm me-2' href='profile.php'>Profil</a>";
        echo "<a class='btn btn-outline-light btn-sm me-2' href='logout.php'>Çıkış</a>";
        if ($_SESSION['role'] == 'admin') {
            echo "<a class='btn btn-light btn-sm' href='admin.php'>Admin Panel</a>";
        }
    } else {
        echo "<a class='btn btn-light btn-sm me-2' href='login.php'>Giriş Yap</a>";
        echo "<a class='btn btn-outline-light btn-sm' href='register.php'>Kayıt Ol</a>";
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sağlık Personeli Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sağlık Personeli Portalı</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php render_menu(); ?>
                </ul>
                <?php render_auth(); ?>
            </div>
        </div>
    </nav>
    <div class="container my-4">
    <section class="card p-4">
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
    </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
