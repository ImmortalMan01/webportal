<?php
function default_roles(): array {
    return [
        'Normal Personel',
        'Sorumlu Hemşire',
        'Klinik Eğitim Hemşiresi',
        'admin'
    ];
}

function get_all_roles(PDO $pdo): array {
    $dbRoles = $pdo->query('SELECT DISTINCT role FROM users')->fetchAll(PDO::FETCH_COLUMN);
    $roles = array_unique(array_merge(default_roles(), $dbRoles));
    sort($roles);
    return $roles;
}
?>
