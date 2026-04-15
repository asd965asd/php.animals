<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$stmt = $pdo->prepare('SELECT ads.*, breeds.name AS breed_name, districts.name AS district_name FROM ads JOIN breeds ON breeds.id = ads.breed_id JOIN districts ON districts.id = ads.district_id WHERE ads.user_id = ? ORDER BY ads.created_at DESC');
$stmt->execute([current_user()['id']]);
$ads = $stmt->fetchAll();

$pageTitle = 'Мои объявления';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Мои объявления</h1>
    <a href="create_ad.php" class="btn btn-primary">+ Новое объявление</a>
</div>
<div class="row g-4">
<?php foreach ($ads as $ad): ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <img src="<?= e($ad['photo_path'] ?: 'https://placehold.co/640x400?text=No+Photo') ?>" class="card-img-top" alt="Фото" style="height: 220px; object-fit: cover;">
            <div class="card-body">
                <h5><?= e($ad['title']) ?></h5>
                <p class="text-muted"><?= e($ad['breed_name']) ?> · <?= e($ad['district_name']) ?></p>
                <p><?= e(mb_strimwidth($ad['description'], 0, 150, '...')) ?></p>
                <span class="badge text-bg-secondary"><?= e(format_status($ad['status'])) ?></span>
            </div>
            <div class="card-footer bg-white d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-primary btn-sm" href="ad.php?id=<?= (int)$ad['id'] ?>">Открыть</a>
                <a class="btn btn-warning btn-sm" href="edit_ad.php?id=<?= (int)$ad['id'] ?>">✏️ Редактировать</a>
                <?php if ($ad['status'] !== 'closed'): ?>
                    <form action="delete_ad.php" method="POST" onsubmit="return confirm('Снять объявление с публикации?');">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= (int)$ad['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm">🗑️ Снять</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php if (!$ads): ?><div class="col-12"><div class="alert alert-light border">У вас пока нет объявлений.</div></div><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
