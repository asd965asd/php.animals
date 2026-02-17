<?php
// actions/admin/lost_request_decide.php — approve/reject заявок владельцев

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    header('Location: index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=admin_lost_requests');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
) {
    http_response_code(400);
    die('CSRF blocked');
}

$id = (int)($_POST['id'] ?? 0);
$action = (string)($_POST['decision'] ?? '');
if ($id <= 0 || ($action !== 'approve' && $action !== 'reject')) {
    header('Location: index.php?page=admin_lost_requests');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lost_pet_requests WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: index.php?page=admin_lost_requests');
    exit;
}

if ($action === 'approve') {
    // создаем публичное объявление
    $ins = $pdo->prepare("
        INSERT INTO posts (type_animal, breed_animal, coloring_animal, district, description, image_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([
        $row['type_animal'],
        $row['breed_animal'],
        $row['coloring_animal'],
        $row['district'],
        $row['description'],
        $row['image_url'],
    ]);
    $upd = $pdo->prepare("UPDATE lost_pet_requests SET status = 'approved' WHERE id = ?");
    $upd->execute([$id]);
} else {
    $upd = $pdo->prepare("UPDATE lost_pet_requests SET status = 'rejected' WHERE id = ?");
    $upd->execute([$id]);
}

header('Location: index.php?page=admin_lost_requests');
exit;

