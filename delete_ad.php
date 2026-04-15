<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

if (!is_post()) {
    http_response_code(405);
    exit('Метод не поддерживается.');
}

verify_csrf();

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, user_id FROM ads WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$ad = $stmt->fetch();

if (!$ad) {
    flash('error', 'Объявление не найдено.');
    redirect('index.php');
}

if (!is_admin() && (int)$ad['user_id'] !== (int)current_user()['id']) {
    http_response_code(403);
    exit('У вас нет прав на удаление этого объявления.');
}

$stmt = $pdo->prepare('UPDATE ads SET status = ? WHERE id = ?');
$stmt->execute(['closed', $id]);

flash('success', 'Объявление снято с публикации.');
redirect(is_admin() ? 'admin_ads.php' : 'my_ads.php');
