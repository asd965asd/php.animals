<?php
// actions/orders/make_order.php — создать заявку (POST+CSRF)

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=home');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
) {
    http_response_code(400);
    die('Ошибка безопасности: неверный CSRF-токен.');
}

$post_id = (int)($_POST['post_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($post_id <= 0) {
    header('Location: index.php?page=home');
    exit;
}

$check = $pdo->prepare("SELECT id FROM posts WHERE id = ? LIMIT 1");
$check->execute([$post_id]);
if (!$check->fetch()) {
    http_response_code(404);
    die('Объявление не найдено.');
}

$dup = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND post_id = ?");
$dup->execute([$user_id, $post_id]);
if ((int)$dup->fetchColumn() > 0) {
    header('Location: index.php?page=profile');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $post_id]);
} catch (PDOException $e) {
    log_exception($e, 'orders/make_order: insert');
    http_response_code(500);
    die('Ошибка базы данных. Попробуйте позже.');
}

header('Location: index.php?page=profile');
exit;

