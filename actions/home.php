<?php
// actions/home.php — загрузка списка объявлений (поиск + пагинация)

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$district = isset($_GET['district']) ? trim((string)$_GET['district']) : '';

$where = array();
$params = array();

if ($q !== '') {
    $where[] = "(type_animal LIKE ? OR breed_animal LIKE ? OR description LIKE ?)";
    $search = '%' . $q . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if ($district !== '') {
    $where[] = "district = ?";
    $params[] = $district;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = ' WHERE ' . implode(' AND ', $where);
}

$currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($currentPage < 1) $currentPage = 1;
$limit = 10;
$offset = ($currentPage - 1) * $limit;

$countSql = "SELECT COUNT(*) FROM posts" . $whereSql;
$total_stmt = $pdo->prepare($countSql);
$total_stmt->execute($params);
$total_rows = (int)$total_stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total_rows / $limit));

$sql = "SELECT id, type_animal, breed_animal, coloring_animal, district, description, image_url
        FROM posts" . $whereSql . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

