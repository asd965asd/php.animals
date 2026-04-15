<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM ads WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$ad = $stmt->fetch();

if (!$ad) {
    http_response_code(404);
    exit('Объявление не найдено.');
}

$user = current_user();
if (!is_admin() && (int)$ad['user_id'] !== (int)$user['id']) {
    http_response_code(403);
    exit('У вас нет прав на редактирование этого объявления.');
}

$breeds = $pdo->query('SELECT id, name FROM breeds ORDER BY name')->fetchAll();
$districts = $pdo->query('SELECT id, name FROM districts ORDER BY name')->fetchAll();

$error = '';

if (is_post()) {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pet_name = trim($_POST['pet_name'] ?? '');
    $breed_id = (int)($_POST['breed_id'] ?? 0);
    $district_id = (int)($_POST['district_id'] ?? 0);
    $status = $_POST['status'] ?? 'lost';
    $address = trim($_POST['address'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');

    if ($title === '' || $description === '' || $breed_id <= 0 || $district_id <= 0) {
        $error = 'Заполните обязательные поля.';
    } elseif (!in_array($status, ['lost', 'found', 'closed'], true)) {
        $error = 'Неверный статус.';
    } else {
        $photoPath = $ad['photo_path'];
        try {
            if (isset($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $photoPath = upload_image($_FILES['photo']);
            }

            $stmt = $pdo->prepare('UPDATE ads SET title = ?, pet_name = ?, description = ?, breed_id = ?, district_id = ?, status = ?, photo_path = ?, address = ?, latitude = ?, longitude = ? WHERE id = ?');
            $stmt->execute([
                $title,
                $pet_name,
                $description,
                $breed_id,
                $district_id,
                $status,
                $photoPath,
                $address,
                $latitude !== '' ? $latitude : null,
                $longitude !== '' ? $longitude : null,
                $id
            ]);

            flash('success', 'Объявление обновлено.');
            redirect('ad.php?id=' . $id);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Редактировать объявление';
require __DIR__ . '/includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h3 mb-3">Редактировать объявление</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_input() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Заголовок</label>
                    <input name="title" class="form-control" required value="<?= e($_POST['title'] ?? $ad['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Кличка животного</label>
                    <input name="pet_name" class="form-control" value="<?= e($_POST['pet_name'] ?? $ad['pet_name']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Порода</label>
                    <select name="breed_id" class="form-select" required>
                        <option value="">Выберите</option>
                        <?php $selectedBreed = (int)($_POST['breed_id'] ?? $ad['breed_id']); ?>
                        <?php foreach ($breeds as $breed): ?>
                            <option value="<?= (int)$breed['id'] ?>" <?= $selectedBreed === (int)$breed['id'] ? 'selected' : '' ?>><?= e($breed['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Район</label>
                    <select name="district_id" class="form-select" required>
                        <option value="">Выберите</option>
                        <?php $selectedDistrict = (int)($_POST['district_id'] ?? $ad['district_id']); ?>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= (int)$district['id'] ?>" <?= $selectedDistrict === (int)$district['id'] ? 'selected' : '' ?>><?= e($district['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Статус</label>
                    <?php $selectedStatus = $_POST['status'] ?? $ad['status']; ?>
                    <select name="status" class="form-select">
                        <option value="lost" <?= $selectedStatus === 'lost' ? 'selected' : '' ?>>Пропало</option>
                        <option value="found" <?= $selectedStatus === 'found' ? 'selected' : '' ?>>Найдено</option>
                        <option value="closed" <?= $selectedStatus === 'closed' ? 'selected' : '' ?>>Снято с публикации</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Новое фото</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    <small class="text-muted">Оставьте пустым, чтобы сохранить текущее фото.</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="4" required><?= e($_POST['description'] ?? $ad['description']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Адрес</label>
                    <input name="address" class="form-control" value="<?= e($_POST['address'] ?? $ad['address']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Широта</label>
                    <input name="latitude" class="form-control" value="<?= e($_POST['latitude'] ?? $ad['latitude']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Долгота</label>
                    <input name="longitude" class="form-control" value="<?= e($_POST['longitude'] ?? $ad['longitude']) ?>">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button class="btn btn-primary">Сохранить изменения</button>
                <a href="ad.php?id=<?= (int)$ad['id'] ?>" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
