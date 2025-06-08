<?php
function update_activity(PDO $pdo){
    if(isset($_SESSION['user'])){
        $stmt = $pdo->prepare('UPDATE users SET last_active=NOW() WHERE username=?');
        $stmt->execute([$_SESSION['user']]);
    }
}

function log_activity(PDO $pdo, string $action, string $details = ''): void {
    if (!isset($_SESSION['user'])) {
        return;
    }

    // Ensure log table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        details TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Translate simple action codes to descriptive Turkish texts
    $map = [
        'login'                    => 'Sisteme giriş yaptı',
        'profile_password_change'  => 'Şifresini değiştirdi',
        'profile_add_experience'   => 'Deneyim ekledi',
        'profile_delete_experience'=> 'Deneyim sildi',
        'profile_update'           => 'Profilini güncelledi'
    ];

    if (isset($map[$action])) {
        $actionText = $map[$action];
        // Embed details for experience logs
        if ($action === 'profile_add_experience' && $details !== '') {
            $actionText .= ' (' . $details . ')';
            $details = '';
        } elseif ($action === 'profile_delete_experience' && $details !== '') {
            $actionText .= ' (ID: ' . $details . ')';
            $details = '';
        }
    } elseif (str_starts_with($action, 'admin_')) {
        // Parse admin actions of the form admin_section_action
        $parts = explode('_', $action, 3);
        $section = $parts[1] ?? '';
        $act = $parts[2] ?? '';

        $sectionMap = [
            'users'          => 'kullanıcılar',
            'shifts'         => 'nöbetler',
            'trainings'      => 'eğitimler',
            'exams'          => 'sınavlar',
            'procedures'     => 'prosedürler',
            'modules'        => 'modüller',
            'nav_links'      => 'menü bağlantıları',
            'pages'          => 'sayfalar',
            'announcements'  => 'duyurular',
            'experiences'    => 'deneyimler',
            'messages'       => 'mesajlar',
            'admin_messages' => 'yönetici mesajları',
            'profiles'       => 'profiller',
            'settings'       => 'ayarlar'
        ];

        $actionMap = [
            'add'        => 'ekledi',
            'delete'     => 'sildi',
            'update'     => 'güncelledi',
            'changerole' => 'rol değiştirdi',
            'send'       => 'mesaj gönderdi',
            'enable'     => 'etkinleştirdi',
            'disable'    => 'devre dışı bıraktı'
        ];

        $sectionTr = $sectionMap[$section] ?? $section;
        $actionTr  = $actionMap[$act] ?? $act;
        $actionText = 'Admin: ' . $sectionTr . ' bölümünde ' . $actionTr;
    } else {
        $actionText = $action; // unknown action
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
    $stmt->execute([$_SESSION['user']]);
    $uid = $stmt->fetchColumn();
    if ($uid) {
        $ins = $pdo->prepare('INSERT INTO activity_log (user_id, action, details) VALUES (?,?,?)');
        $ins->execute([$uid, $actionText, $details]);
    }
}
?>
