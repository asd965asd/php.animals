<?php
// actions/admin/orders.php — список заявок (JOIN)

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    header('Location: index.php?page=login');
    exit;
}

$sql = "
    SELECT 
        orders.id as order_id,
        orders.created_at,
        users.email,
        posts.type_animal,
        posts.breed_animal,
        posts.district
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN posts ON orders.post_id = posts.id
    ORDER BY orders.id DESC
";

$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll();

