<?php
// Simple license management
function license_setup(PDO $pdo){
    $pdo->exec("CREATE TABLE IF NOT EXISTS license (
        id INT PRIMARY KEY,
        license_key VARCHAR(255) NOT NULL,
        verified TINYINT(1) NOT NULL DEFAULT 0
    )");
}

function generate_license_key(): string {
    // Generate expected license for current domain
    $secret = 'MY_SECRET_SALT';
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return hash('sha256', $domain . $secret);
}

function is_license_valid(PDO $pdo): bool {
    license_setup($pdo);
    $stmt = $pdo->query("SELECT verified FROM license WHERE id=1");
    return $stmt->fetchColumn() == 1;
}

function verify_license(PDO $pdo, string $key): bool {
    $expected = generate_license_key();
    if (hash_equals($expected, $key)) {
        license_setup($pdo);
        $stmt = $pdo->prepare("REPLACE INTO license (id, license_key, verified) VALUES (1, ?, 1)");
        $stmt->execute([$key]);
        return true;
    }
    return false;
}

function enforce_license(PDO $pdo){
    if (basename($_SERVER['SCRIPT_NAME']) === 'activate.php') {
        return;
    }
    if (!is_license_valid($pdo)) {
        header('Location: /activate.php');
        exit;
    }
}
?>
