<?php
// actions/admin/lost_requests.php — модерация заявок владельцев

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    header('Location: index.php?page=login');
    exit;
}

$pdo->exec("
    CREATE TABLE IF NOT EXISTS lost_pet_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type_animal VARCHAR(100) NOT NULL,
        breed_animal VARCHAR(100) DEFAULT NULL,
        coloring_animal VARCHAR(100) DEFAULT NULL,
        district VARCHAR(120) NOT NULL,
        description TEXT,
        image_url VARCHAR(255) NOT NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$stmt = $pdo->query("
    SELECT r.*, u.email
    FROM lost_pet_requests r
    LEFT JOIN users u ON u.id = r.user_id
    ORDER BY r.id DESC
");
$lostRequests = $stmt->fetchAll();

