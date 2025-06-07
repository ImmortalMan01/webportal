<?php
function create_reset_token(PDO $pdo, int $userId): string {
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $token = bin2hex(random_bytes(16));
    $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour
    $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)');
    $stmt->execute([$userId, $token, $expiry]);
    return $token;
}

function get_user_id_by_token(PDO $pdo, string $token): ?int {
    $pdo->prepare('DELETE FROM password_resets WHERE expires_at < NOW()')->execute();
    $stmt = $pdo->prepare('SELECT user_id FROM password_resets WHERE token=? AND expires_at >= NOW()');
    $stmt->execute([$token]);
    $uid = $stmt->fetchColumn();
    return $uid !== false ? (int)$uid : null;
}

function invalidate_token(PDO $pdo, string $token): void {
    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE token=?');
    $stmt->execute([$token]);
}

function update_user_password(PDO $pdo, int $userId, string $password): void {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
    $stmt->execute([$hash, $userId]);
}
?>
