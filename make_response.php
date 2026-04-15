<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$adId = (int)($_GET['id'] ?? 0);
$currentUser = current_user();

if ($adId <= 0) {
    http_response_code(400);
    exit('Неверное объявление. <a href="index.php">Вернуться</a>');
}

$check = $pdo->prepare('SELECT id, user_id, title, status FROM ads WHERE id = ? LIMIT 1');
$check->execute([$adId]);
$ad = $check->fetch();

if (!$ad) {
    http_response_code(404);
    exit('Ошибка: попытка откликнуться на несуществующее объявление. <a href="index.php">Вернуться</a>');
}

if ((int)$ad['user_id'] === (int)$currentUser['id']) {
    exit('Нельзя откликаться на собственное объявление. <a href="ad.php?id=' . $adId . '">Назад</a>');
}

if ($ad['status'] === 'closed') {
    exit('Это объявление уже закрыто. <a href="ad.php?id=' . $adId . '">Назад</a>');
}

$recentCheck = $pdo->prepare('SELECT id FROM ad_responses WHERE user_id = ? AND ad_id = ? AND created_at >= (NOW() - INTERVAL 5 MINUTE) LIMIT 1');
$recentCheck->execute([$currentUser['id'], $adId]);
if ($recentCheck->fetch()) {
    exit('Вы уже откликались на это объявление менее 5 минут назад. <a href="ad.php?id=' . $adId . '">Назад</a>');
}

$stmt = $pdo->prepare('INSERT INTO ad_responses (user_id, ad_id, status) VALUES (?, ?, ?)');

try {
    $stmt->execute([$currentUser['id'], $adId, 'new']);
    flash('success', 'Отклик успешно отправлен по объявлению: ' . $ad['title']);
    redirect('ad.php?id=' . $adId);
} catch (PDOException $e) {
    exit('Ошибка БД: ' . e($e->getMessage()));
}
