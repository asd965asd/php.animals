<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$q = trim($_GET['q'] ?? '');

$sql = '
    SELECT ads.*, breeds.name AS breed_name, districts.name AS district_name, users.email, users.username
    FROM ads
    JOIN breeds ON breeds.id = ads.breed_id
    JOIN districts ON districts.id = ads.district_id
    JOIN users ON users.id = ads.user_id
';
$params = [];
$where = [];

if ($q !== '') {
    $where[] = '(ads.title LIKE ? OR ads.pet_name LIKE ? OR users.email LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY ads.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll();

$pageTitle = 'Управление объявлениями';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0">Управление объявлениями</h1>
        <p class="text-muted mb-0">Админ может найти, изменить и снять с публикации любое объявление.</p>
    </div>
    <a href="create_ad.php" class="btn btn-primary">+ Новое объявление</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body bg-light">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <label class="form-label">Поиск</label>
                <input type="text" name="q" class="form-control" placeholder="Заголовок, кличка или email автора" value="<?= e($q) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-dark w-100">Найти</button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle bg-white shadow-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Фото</th>
                <th>Объявление</th>
                <th>Порода / Район</th>
                <th>Автор</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ads as $ad): ?>
                <tr>
                    <td><?= (int)$ad['id'] ?></td>
                    <td><img src="<?= e($ad['photo_path'] ?: 'https://placehold.co/120x90?text=No+Photo') ?>" alt="" style="width:120px;height:90px;object-fit:cover" class="rounded"></td>
                    <td>
                        <strong><?= e($ad['title']) ?></strong><br>
                        <small class="text-muted"><?= e($ad['pet_name'] ?: 'Без клички') ?></small>
                    </td>
                    <td><?= e($ad['breed_name']) ?> / <?= e($ad['district_name']) ?></td>
                    <td><?= e($ad['username'] ?: 'Пользователь') ?><br><small class="text-muted"><?= e($ad['email']) ?></small></td>
                    <td><span class="badge text-bg-secondary"><?= e(format_status($ad['status'])) ?></span></td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="ad.php?id=<?= (int)$ad['id'] ?>" class="btn btn-outline-primary btn-sm">Открыть</a>
                            <a href="edit_ad.php?id=<?= (int)$ad['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
                            <?php if ($ad['status'] !== 'closed'): ?>
                                <form action="delete_ad.php" method="POST" onsubmit="return confirm('Снять объявление с публикации?');">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int)$ad['id'] ?>">
                                    <button class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$ads): ?>
                <tr><td colspan="7" class="text-center text-muted">Объявления не найдены.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
