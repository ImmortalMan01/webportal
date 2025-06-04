<?php
function update_activity(PDO $pdo){
    if(isset($_SESSION['user'])){
        $stmt = $pdo->prepare('UPDATE users SET last_active=NOW() WHERE username=?');
        $stmt->execute([$_SESSION['user']]);
    }
}
?>
