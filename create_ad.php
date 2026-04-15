<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$error = '';
$breeds = $pdo->query('SELECT id, name FROM breeds ORDER BY name')->fetchAll();
$districts = $pdo->query('SELECT id, name FROM districts ORDER BY name')->fetchAll();

if (is_post()) {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pet_name = trim($_POST['pet_name'] ?? '');
    $breed_id = (int)($_POST['breed_id'] ?? 0);
    $district_id = (int)($_POST['district_id'] ?? 0);
    $address = trim($_POST['address'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');

    if ($title === '' || $description === '' || $breed_id <= 0 || $district_id <= 0) {
        $error = 'Заполните обязательные поля.';
    } elseif (($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Фото питомца обязательно.';
    } else {
        try {
            $photoPath = upload_image($_FILES['photo'] ?? []);

            $stmt = $pdo->prepare('
                INSERT INTO ads (
                    user_id,
                    title,
                    pet_name,
                    description,
                    breed_id,
                    district_id,
                    status,
                    photo_path,
                    address,
                    latitude,
                    longitude
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                current_user()['id'],
                $title,
                $pet_name,
                $description,
                $breed_id,
                $district_id,
                'lost',
                $photoPath,
                $address,
                $latitude !== '' ? $latitude : null,
                $longitude !== '' ? $longitude : null
            ]);

            flash('success', 'Объявление успешно создано.');
            redirect('my_ads.php');
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Подать объявление';
require __DIR__ . '/includes/header.php';
?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <h1 class="h3 mb-3">Подать объявление</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <?= csrf_input() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Заголовок</label>
                    <input name="title" class="form-control" required value="<?= old('title') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Кличка животного</label>
                    <input name="pet_name" class="form-control" value="<?= old('pet_name') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Порода</label>
                    <select name="breed_id" class="form-select" required>
                        <option value="">Выберите</option>
                        <?php foreach ($breeds as $breed): ?>
                            <option value="<?= (int)$breed['id'] ?>" <?= ((int)($_POST['breed_id'] ?? 0) === (int)$breed['id']) ? 'selected' : '' ?>>
                                <?= e($breed['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Район</label>
                    <select name="district_id" class="form-select" required>
                        <option value="">Выберите</option>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= (int)$district['id'] ?>" <?= ((int)($_POST['district_id'] ?? 0) === (int)$district['id']) ? 'selected' : '' ?>>
                                <?= e($district['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Фото</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" required>
                    <small class="text-muted">Загрузите фото питомца. Разрешены JPG, PNG, GIF, WEBP до 5 МБ.</small>
                </div>

                <div class="col-12">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="4" required><?= old('description') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Адрес / ориентир</label>
                    <input name="address" class="form-control" value="<?= old('address') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Широта</label>
                    <input name="latitude" id="latitude" class="form-control" value="<?= old('latitude') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Долгота</label>
                    <input name="longitude" id="longitude" class="form-control" value="<?= old('longitude') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Точка на карте</label>
                    <div id="map" class="map-box"></div>
                    <small class="text-muted">Кликни по карте, чтобы поставить точку.</small>
                </div>
            </div>

            <button class="btn btn-success mt-4">Сохранить объявление</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    const defaultLat = latInput.value ? parseFloat(latInput.value) : 55.7558;
    const defaultLng = lngInput.value ? parseFloat(lngInput.value) : 37.6176;

    const map = L.map('map').setView([defaultLat, defaultLng], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = null;

    if (latInput.value && lngInput.value) {
        marker = L.marker([defaultLat, defaultLng]).addTo(map);
    }

    map.on('click', function (e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);

        latInput.value = lat;
        lngInput.value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>