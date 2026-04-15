<?php
// actions/orders/cancel.php — отмена заявки (POST+CSRF+Anti-IDOR)

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=profile');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
) {
    http_response_code(400);
    die('Ошибка безопасности: неверный CSRF-токен.');
}

$order_id = (int)($_POST['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
if ($order_id <= 0) {
    header('Location: index.php?page=profile');
    exit;
}

// Удаляем только свою заявку и только новую
$stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ? AND status = 'new'");
$stmt->execute([$order_id, $user_id]);

header('Location: index.php?page=profile');
exit;

