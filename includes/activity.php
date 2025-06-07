<?php
function update_activity(PDO $pdo){
    if(isset($_SESSION['user'])){
        $stmt = $pdo->prepare('UPDATE users SET last_active=NOW() WHERE username=?');
        $stmt->execute([$_SESSION['user']]);
    }
}

function log_activity(PDO $pdo, string $action, string $details = ''): void {
    if(!isset($_SESSION['user'])){
        return;
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        details TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
    $stmt->execute([$_SESSION['user']]);
    $uid = $stmt->fetchColumn();
    if($uid){
        $ins = $pdo->prepare('INSERT INTO activity_log (user_id, action, details) VALUES (?,?,?)');
        $ins->execute([$uid, $action, $details]);
    }
}
?>
