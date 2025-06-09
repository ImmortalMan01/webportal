<?php
session_start();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/license.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim($_POST['license_key'] ?? '');
    if ($key !== '' && verify_license($pdo, $key)) {
        header('Location: index.php');
        exit;
    } else {
        $message = 'Lisans anahtarı geçersiz.';
    }
}
$expected = generate_license_key();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lisans Aktivasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Lisans Aktivasyonu</h3>
                    <?php if($message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="license_key" class="form-label">Lisans Anahtarı</label>
                            <input type="text" class="form-control" name="license_key" id="license_key" required>
                        </div>
                        <button class="btn btn-primary">Aktive Et</button>
                    </form>
                    <p class="mt-3 text-muted">Bu demo için beklenen anahtar: <code><?php echo $expected; ?></code></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
