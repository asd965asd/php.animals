<?php
// actions/profile/order_details.php — детали заявки (Anti-IDOR)

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    http_response_code(404);
    die('Заявка не найдена.');
}

$sql = "
    SELECT
        orders.id as order_id,
        orders.created_at,
        orders.status,
        posts.type_animal,
        posts.breed_animal,
        posts.coloring_animal,
        posts.district,
        posts.description,
        posts.image_url
    FROM orders
    JOIN posts ON orders.post_id = posts.id
    WHERE orders.id = ? AND orders.user_id = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    die('Заявка не найдена.');
}

$img = trim((string)($order['image_url'] ?? ''));
if ($img === '') $img = "https://via.placeholder.com/600x400?text=Нет+фото";

