<?php
session_start();
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
function render_menu() {
    echo "<ul>";
    echo "<li><a href='?module=shift'>Vardiya Sistemi</a></li>";
    echo "<li><a href='?module=training'>Eğitimler</a></li>";
    echo "<li><a href='?module=exam'>Sınavlar</a></li>";
    echo "<li><a href='?module=procedure'>Prosedürler</a></li>";
    echo "</ul>";
}

function render_auth() {
    if (isset($_SESSION['user'])) {
        echo "<p>Merhaba " . htmlspecialchars($_SESSION['user']) . " | <a href='logout.php'>Çıkış</a>";
        if ($_SESSION['role'] == 'admin') {
            echo " | <a href='admin.php'>Admin Panel</a>";
        }
        echo "</p>";
    } else {
        echo "<p><a href='login.php'>Giriş Yap</a> | <a href='register.php'>Kayıt Ol</a></p>";
    }
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Sağlık Personeli Portalı</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Sağlık Personeli Portalı</h1>
        <nav>
            <?php render_menu(); ?>
        </nav>
        <?php render_auth(); ?>
    </header>
    <div class="container">
    <section>
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
</body>
</html>
