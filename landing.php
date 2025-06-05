<?php
session_start();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/settings.php';
require __DIR__ . '/includes/pages.php';
$site_name = get_setting($pdo, 'site_name', 'Portal');
$pages = get_public_pages($pdo);
$slug = $_GET['p'] ?? 'home';
$page = get_page_content($pdo, $slug);
?>
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $title = $page['title'] ?? $site_name; ?>
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="landing.php"><?php echo htmlspecialchars($site_name); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach($pages as $p): ?>
                    <li class="nav-item"><a class="nav-link <?php if($slug==$p['slug']) echo 'active'; ?>" href="landing.php?p=<?php echo htmlspecialchars($p['slug']); ?>"><?php echo htmlspecialchars($p['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-light btn-sm" href="pages/login.php">Giriş Yap</a>
        </div>
    </div>
</nav>
<div class="container my-4">
    <div class="card p-4">
        <?php echo $page ? $page['content'] : '<p>Sayfa bulunamadı.</p>'; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
