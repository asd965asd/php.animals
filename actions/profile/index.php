<?php
// actions/profile/index.php — личный кабинет (мои заявки + аватар)

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$profileError = '';

// Аватар (если колонки avatar_url нет — покажем заглушку)
$avatarUrl = '';
try {
    $u = $pdo->prepare("SELECT avatar_url FROM users WHERE id = ? LIMIT 1");
    $u->execute([$user_id]);
    $row = $u->fetch();
    if ($row && !empty($row['avatar_url'])) {
        $avatarUrl = trim((string)$row['avatar_url']);
    }
} catch (PDOException $e) {
    $avatarUrl = '';
}
if ($avatarUrl === '') {
    $avatarUrl = 'https://via.placeholder.com/80?text=Avatar';
}

$sql = "
    SELECT 
        orders.id as order_id, 
        orders.created_at, 
        orders.status, 
        posts.type_animal, 
        posts.breed_animal,
        posts.district,
        posts.image_url
    FROM orders 
    JOIN posts ON orders.post_id = posts.id 
    WHERE orders.user_id = ? 
    ORDER BY orders.created_at DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $my_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    log_exception($e, 'profile: load my orders');
    $my_orders = array();
    $profileError = 'Ошибка базы данных при загрузке заявок. Попробуйте позже.';
}
