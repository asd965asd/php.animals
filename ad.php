<?php
require __DIR__ . '/includes/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT ads.*, breeds.name AS breed_name, districts.name AS district_name, users.username, users.phone, users.email FROM ads JOIN breeds ON breeds.id = ads.breed_id JOIN districts ON districts.id = ads.district_id JOIN users ON users.id = ads.user_id WHERE ads.id = ? LIMIT 1');
$stmt->execute([$id]);
$ad = $stmt->fetch();
if (!$ad) {
    http_response_code(404);
    exit('Объявление не найдено.');
}
$canManage = is_logged_in() && (is_admin() || (int)$ad['user_id'] === (int)current_user()['id']);
$pageTitle = $ad['title'];
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-6">
        <img src="<?= e($ad['photo_path'] ?: 'https://placehold.co/900x600?text=No+Photo') ?>" alt="Фото" class="img-fluid rounded-4 shadow-sm w-100">
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h2"><?= e($ad['title']) ?></h1>
                <p class="text-muted">Статус: <strong><?= e(format_status($ad['status'])) ?></strong></p>
                <p><strong>Кличка:</strong> <?= e($ad['pet_name'] ?: 'Не указана') ?></p>
                <p><strong>Порода:</strong> <?= e($ad['breed_name']) ?></p>
                <p><strong>Район:</strong> <?= e($ad['district_name']) ?></p>
                <p><strong>Адрес:</strong> <?= e($ad['address'] ?: 'Не указан') ?></p>
                <p><strong>Описание:</strong><br><?= nl2br(e($ad['description'])) ?></p>
                <hr>
                <p class="mb-1"><strong>Контактное лицо:</strong> <?= e($ad['username'] ?: 'Пользователь') ?></p>
                <p class="mb-1"><strong>Телефон:</strong> <?= e($ad['phone'] ?: 'Не указан') ?></p>
                <p class="mb-3"><strong>Email:</strong> <?= e($ad['email']) ?></p>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="index.php" class="btn btn-outline-secondary">Назад</a>
                    <?php if (is_logged_in() && (int)$ad['user_id'] !== (int)current_user()['id'] && $ad['status'] !== 'closed'): ?>
                        <a href="make_response.php?id=<?= (int)$ad['id'] ?>" class="btn btn-primary">Откликнуться</a>
                    <?php endif; ?>
                    <?php if ($canManage): ?>
                        <a href="edit_ad.php?id=<?= (int)$ad['id'] ?>" class="btn btn-warning">✏️ Редактировать</a>
                        <?php if ($ad['status'] !== 'closed'): ?>
                            <form action="delete_ad.php" method="POST" onsubmit="return confirm('Снять объявление с публикации?');">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id" value="<?= (int)$ad['id'] ?>">
                                <button class="btn btn-danger">🗑️ Снять</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($ad['latitude'] && $ad['longitude']): ?>
<div class="card shadow-sm mt-4"><div class="card-body"><h2 class="h4">Место на карте</h2><div id="map" class="map-box"></div></div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = <?= json_encode((float)$ad['latitude']) ?>;
    const lng = <?= json_encode((float)$ad['longitude']) ?>;
    const map = L.map('map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.marker([lat, lng]).addTo(map);
});
</script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
