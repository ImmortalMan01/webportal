<?php
function get_public_pages($pdo){
    return $pdo->query("SELECT slug, title FROM site_pages ORDER BY id")->fetchAll();
}

function get_page_content($pdo, $slug){
    $stmt = $pdo->prepare("SELECT title, content FROM site_pages WHERE slug=?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}
?>
