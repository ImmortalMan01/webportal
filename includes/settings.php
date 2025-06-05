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

function role_slug(string $role): string {
    return strtolower(str_replace(' ', '_', $role));
}

function get_role_theme(PDO $pdo, string $role): string {
    $default = $role === 'Normal Personel' ? 'dashboard' : 'classic';
    $key = 'theme_' . role_slug($role);
    return get_setting($pdo, $key, $default);
}

function set_role_theme(PDO $pdo, string $role, string $theme): void {
    $key = 'theme_' . role_slug($role);
    set_setting($pdo, $key, $theme);
}
?>
