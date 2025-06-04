<?php
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
function render_menu() {
    echo "<ul>";
    echo "<li><a href='?module=shift'>Vardiya Sistemi</a></li>";
    echo "<li><a href='?module=training'>Egitimler</a></li>";
    echo "<li><a href='?module=exam'>Sinavlar</a></li>";
    echo "<li><a href='?module=procedure'>Prosedurler</a></li>";
    echo "</ul>";
}
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Sağlık Personeli Portalı</title>
    <style>
        body { font-family: Arial, sans-serif; }
        nav ul { list-style-type: none; padding: 0; }
        nav li { display: inline; margin-right: 10px; }
        section { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Sağlık Personeli Portalı</h1>
    <nav>
        <?php render_menu(); ?>
    </nav>
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
</body>
</html>
