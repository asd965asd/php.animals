<?php
require __DIR__ . '/includes/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$breedId = (int)($_GET['breed_id'] ?? 0);
$districtId = (int)($_GET['district_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;

$breeds = $pdo->query('SELECT id, name FROM breeds ORDER BY name')->fetchAll();
$districts = $pdo->query('SELECT id, name FROM districts ORDER BY name')->fetchAll();

$fromSql = "
    FROM ads
    JOIN breeds ON breeds.id = ads.breed_id
    JOIN districts ON districts.id = ads.district_id
    JOIN users ON users.id = ads.user_id
";
$params = [];
$where = ['ads.status <> ?'];
$params[] = 'closed';

if ($q !== '') {
    $where[] = '(ads.title LIKE ? OR ads.pet_name LIKE ? OR ads.description LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($breedId > 0) {
    $where[] = 'ads.breed_id = ?';
    $params[] = $breedId;
}

if ($districtId > 0) {
    $where[] = 'ads.district_id = ?';
    $params[] = $districtId;
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countSql = 'SELECT COUNT(*) ' . $fromSql . $whereSql;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $limit;

$sql = "
    SELECT ads.*, breeds.name AS breed_name, districts.name AS district_name, users.username
    " . $fromSql . $whereSql . "
    ORDER BY ads.created_at DESC
    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll();

$queryBase = $_GET;
unset($queryBase['page']);
$paginationBase = http_build_query($queryBase);

$pageTitle = 'Портал поиска животных';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0">Объявления</h1>
        <p class="text-muted mb-0">Фильтруйте объявления по породе, району или поисковому запросу.</p>
    </div>
    <?php if (is_logged_in()): ?>
        <a href="create_ad.php" class="btn btn-primary">+ Подать объявление</a>
    <?php endif; ?>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body bg-light">
        <form action="index.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Поиск</label>
                <input type="text" name="q" class="form-control" placeholder="Кличка, заголовок, описание..." value="<?= e($q) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Порода</label>
                <select name="breed_id" class="form-select">
                    <option value="0">Все породы</option>
                    <?php foreach ($breeds as $breed): ?>
                        <option value="<?= $breed['id'] ?>" <?= $breedId === (int)$breed['id'] ? 'selected' : '' ?>>
                            <?= e($breed['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Район</label>
                <select name="district_id" class="form-select">
                    <option value="0">Все районы</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?= $district['id'] ?>" <?= $districtId === (int)$district['id'] ? 'selected' : '' ?>>
                            <?= e($district['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Найти</button>
            </div>

            <div class="col-12 text-end">
                <a href="index.php" class="text-muted text-decoration-none small">Сбросить фильтры</a>
            </div>
        </form>
    </div>
</div>

<?php if ($ads): ?>
<div class="row g-4">
<?php foreach ($ads as $ad): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <img src="<?= e($ad['photo_path'] ?: 'https://placehold.co/640x400?text=No+Photo') ?>" class="card-img-top" alt="Фото" style="height: 220px; object-fit: cover;">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <h2 class="h5 mb-0"><?= e($ad['title']) ?></h2>
                    <span class="badge text-bg-secondary"><?= e(format_status($ad['status'])) ?></span>
                </div>
                <p class="text-muted small mb-2"><?= e($ad['breed_name']) ?> · <?= e($ad['district_name']) ?></p>
                <p class="small mb-2"><strong>Кличка:</strong> <?= e($ad['pet_name'] ?: 'Не указана') ?></p>
                <p class="mb-3"><?= e(mb_strimwidth($ad['description'], 0, 140, '...')) ?></p>
                <div class="mt-auto d-flex gap-2 flex-wrap">
                    <a href="ad.php?id=<?= (int)$ad['id'] ?>" class="btn btn-outline-primary btn-sm">Подробнее</a>
                    <?php if (is_logged_in() && (int)$ad['user_id'] !== (int)current_user()['id']): ?>
                        <a href="make_response.php?id=<?= (int)$ad['id'] ?>" class="btn btn-primary btn-sm">Откликнуться</a>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <a href="edit_ad.php?id=<?= (int)$ad['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small">
                Автор: <?= e($ad['username'] ?: 'Пользователь') ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center flex-wrap">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php $href = 'index.php?' . ($paginationBase ? $paginationBase . '&' : '') . 'page=' . $i; ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($href) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-secondary">По вашему запросу ничего не найдено. Попробуйте изменить фильтры.</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
