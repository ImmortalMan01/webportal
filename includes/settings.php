<?php
function get_setting(PDO $pdo, string $name, $default = null){
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE name=?');
    $stmt->execute([$name]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}
function set_setting(PDO $pdo, string $name, $value){
    $stmt = $pdo->prepare('REPLACE INTO settings (name, value) VALUES (?, ?)');
    $stmt->execute([$name, $value]);
}
?>
