<?php
// Database connection using PDO
$host = 'localhost';
$db   = 'webportal';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // ensure email column exists for backward compatibility
    $c = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'")->fetch();
    if (!$c) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) UNIQUE NOT NULL DEFAULT ''");
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_mutes (
        user_id INT NOT NULL,
        muted_user_id INT NOT NULL,
        PRIMARY KEY (user_id, muted_user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (muted_user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {
    die('Veritabanı bağlantı hatası: ' . $e->getMessage());
}
?>
