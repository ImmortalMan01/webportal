<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';
require 'activity.php';
update_activity($pdo);
$current = $_SESSION['user'];
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$current]);
$currentId = $stmt->fetchColumn();
if (!$currentId) die('Kullanıcı bulunamadı');
$targetUser = $_GET['user'] ?? '';
if ($targetUser) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$targetUser]);
    $targetId = $stmt->fetchColumn();
    if (!$targetId) die('Kullanıcı bulunamadı');
    $conv = $pdo->prepare('SELECT m.id, m.sender_id, m.receiver_id, m.message, m.created_at, m.is_read, u.role AS sender_role FROM messages m JOIN users u ON m.sender_id=u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.id');
    $conv->execute([$currentId, $targetId, $targetId, $currentId]);
    $messages = $conv->fetchAll();
    $ids = [];
    foreach ($messages as $m) {
        if ($m['receiver_id'] == $currentId && !$m['is_read']) {
            $ids[] = $m['id'];
        }
    }
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id IN ($placeholders)");
        $stmt->execute($ids);
    }
}
$allUsers = $pdo->query('SELECT username FROM users WHERE username <> ' . $pdo->quote($current) . ' ORDER BY username')->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesajlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-3">Mesajlar</h2>
    <div class="row">
        <div id="usersCol" class="col-md-4">
            <h5>Kullanıcılar</h5>
            <ul class="list-group mb-3">
                <?php foreach ($allUsers as $u): ?>
                    <li class="list-group-item<?php if ($targetUser==$u['username']) echo ' active'; ?>">
                        <a href="messages.php?user=<?php echo urlencode($u['username']); ?>" class="text-decoration-none<?php if ($targetUser==$u['username']) echo ' text-light'; ?>">
                            <?php echo htmlspecialchars($u['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div id="chatCol" class="col-md-8">
            <?php if ($targetUser): ?>
                <?php
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE username=?');
                    $stmt->execute([$targetUser]);
                    $tId = $stmt->fetchColumn();
                    $prof = ['full_name'=>$targetUser,'picture'=>''];
                    if($tId){
                        $pstmt = $pdo->prepare('SELECT full_name, picture FROM profiles WHERE user_id=?');
                        $pstmt->execute([$tId]);
                        $row = $pstmt->fetch();
                        if($row) $prof = array_merge($prof,$row);
                        $s = $pdo->prepare('SELECT last_active FROM users WHERE id=?');
                        $s->execute([$tId]);
                        $lastSeen = $s->fetchColumn();
                    } else {
                        $lastSeen = null;
                    }
                    $fullName = $prof['full_name'] ?: $targetUser;
                    $shortName = mb_substr($fullName,0,10).(mb_strlen($fullName)>10?'…':'');
                    $avatar = $prof['picture'] ? 'uploads/'.htmlspecialchars($prof['picture']) : 'https://via.placeholder.com/40x40?text='.urlencode(mb_substr($fullName,0,1));
                ?>
                <div id="chatHeader" class="chat-header d-flex align-items-center p-2 border-bottom">
                    <a href="view_profile.php?user=<?php echo urlencode($targetUser); ?>" class="d-flex align-items-center text-decoration-none text-dark">
                        <div class="position-relative me-2">
                            <img src="<?php echo $avatar; ?>" class="rounded-circle" width="40" height="40" alt="avatar">
                            <span id="onlineDot" class="position-absolute bg-success border border-light rounded-circle d-none"></span>
                        </div>
                        <span class="full-name fw-bold"><?php echo htmlspecialchars($fullName); ?></span>
                        <span class="short-name fw-bold"><?php echo htmlspecialchars($shortName); ?></span>
                    </a>
                    <div id="statusText" class="ms-2 small text-muted"></div>
                </div>
                <div id="chatBox" class="border p-3 mb-3"></div>
                <form id="msgForm" class="d-flex">
                    <input type="text" name="message" id="msgInput" class="form-control me-2" placeholder="Mesaj yaz..." autocomplete="off" required>
                    <button class="btn btn-primary">Gönder</button>
                </form>
                <script>
                const partner = <?php echo json_encode($targetUser); ?>;
                const currentUser = <?php echo json_encode($current); ?>;
                const currentId = <?php echo $currentId; ?>;
                let lastSeen = <?php echo json_encode($lastSeen); ?>;
                const chatBox = document.getElementById('chatBox');
                const ws = new WebSocket(`ws://${location.hostname}:8080?user=${encodeURIComponent(currentUser)}`);
                let onlineUsers = new Set();
                const onlineDot = document.getElementById('onlineDot');
                const statusText = document.getElementById('statusText');
                ws.addEventListener('open', () => {
                    ws.send(JSON.stringify({type:'history', with: partner}));
                });
                ws.addEventListener('message', e => {
                    const data = JSON.parse(e.data);
                    if(data.type==='message'){
                        appendMsg(data.from===currentUser, data.text, data.time, data.id, data.status, data.role);
                        if(data.from!==currentUser){
                            ws.send(JSON.stringify({type:'seen', id:data.id, to:data.from}));
                        }
                    }else if(data.type==='typing'){
                        showTyping();
                    }else if(data.type==='seen'){
                        updateStatus(data.id, 'seen');
                    }else if(data.type==='history'){
                        data.messages.forEach(m=>{
                           appendMsg(m.sender_id==currentId, m.message, m.created_at, m.id, m.is_read? 'seen':'delivered', m.sender_role);
                        });
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }else if(data.type==='presence'){
                        if(data.users){
                            onlineUsers = new Set(data.users);
                        }else{
                            if(data.online) onlineUsers.add(data.user); else onlineUsers.delete(data.user);
                        }
                        updateOnline();
                    }
                });

                setInterval(()=>{
                    fetch('last_active.php?user='+encodeURIComponent(partner))
                        .then(r=>r.json())
                        .then(d=>{lastSeen=d.last_active; updateOnline();});
                },10000);

                document.getElementById('msgForm').addEventListener('submit', e=>{
                    e.preventDefault();
                    const input = document.getElementById('msgInput');
                    const text = input.value.trim();
                    if(!text) return;
                    ws.send(JSON.stringify({type:'message', to: partner, text}));
                    input.value='';
                });

                document.getElementById('msgInput').addEventListener('input', ()=>{
                    ws.send(JSON.stringify({type:'typing', to: partner}));
                });

                function updateOnline(){
                    if(onlineUsers.has(partner)) {
                        onlineDot.classList.remove('d-none');
                        statusText.textContent = 'Çevrim İçi';
                    } else {
                        onlineDot.classList.add('d-none');
                        if(lastSeen) statusText.textContent = 'Son görülme: '+lastSeen;
                        else statusText.textContent = '';
                    }
                }

                function appendMsg(mine, msg, time, id, status, role){
                    const div = document.createElement('div');
                    div.className = 'd-flex '+(mine?'justify-content-end':'justify-content-start');
                    let cls = mine?'mine':'theirs';
                    if(role==='admin') cls+=' admin-msg';
                    div.innerHTML = `<div class="bubble ${cls}" data-id="${id}">`+
                        (role==='admin'?`<div class="sender">Admin</div>`:'')+
                        `<div class="text">${escapeHtml(msg)}</div>`+
                        `<div class="meta"><span class="time">${time}</span> <span class="status">${statusIcon(status)}</span></div>`+
                        `</div>`;
                    chatBox.appendChild(div);
                    chatBox.scrollTop = chatBox.scrollHeight;
                }

                function updateStatus(id, status){
                    const el = chatBox.querySelector(`[data-id="${id}"] .status`);
                    if(el) el.innerHTML = statusIcon(status);
                }

                function statusIcon(status){
                    if(status==='sent') return '&#10003;';
                    if(status==='delivered') return '&#10003;&#10003;';
                    if(status==='seen') return '<span style="color:#0d6efd">&#10003;&#10003;</span>';
                    return '';
                }

                function escapeHtml(str){
                    return str.replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));
                }

                let typingTimer;
                function showTyping(){
                    let el = document.getElementById('typing');
                    if(!el){
                        el = document.createElement('div');
                        el.id='typing';
                        el.className='small text-muted';
                        el.textContent='Yazıyor...';
                        chatBox.appendChild(el);
                    }
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(()=>{el.remove();},2000);
                }
                </script>
            <?php else: ?>
                <p>Soldaki listeden bir kullanıcı seçiniz.</p>
            <?php endif; ?>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary mt-3">Geri</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
