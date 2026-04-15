<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$responseId = (int)($_GET['id'] ?? 0);
$userId = (int)current_user()['id'];

if ($responseId <= 0) {
    http_response_code(400);
    exit('Некорректный идентификатор отклика.');
}

$sql = "
    SELECT
        ad_responses.id AS response_id,
        ad_responses.status AS response_status,
        ad_responses.created_at AS response_created_at,
        ads.id AS ad_id,
        ads.title,
        ads.description,
        ads.status AS ad_status,
        ads.photo_path,
        ads.pet_name,
        ads.address,
        breeds.name AS breed_name,
        districts.name AS district_name,
        owners.username AS owner_name,
        owners.phone AS owner_phone,
        owners.email AS owner_email
    FROM ad_responses
    JOIN ads ON ad_responses.ad_id = ads.id
    JOIN users AS owners ON ads.user_id = owners.id
    LEFT JOIN breeds ON ads.breed_id = breeds.id
    LEFT JOIN districts ON ads.district_id = districts.id
    WHERE ad_responses.id = ? AND ad_responses.user_id = ?
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$responseId, $userId]);
$response = $stmt->fetch();

if (!$response) {
    http_response_code(404);
    exit('Отклик не найден или у вас нет прав на его просмотр.');
}

$pageTitle = 'Детали отклика';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <img src="<?= e($response['photo_path'] ?: 'https://placehold.co/800x500?text=No+Photo') ?>" class="card-img-top" alt="Фото">
            <div class="card-body">
                <h1 class="h3 mb-3"><?= e($response['title']) ?></h1>
                <p class="mb-2"><strong>Кличка:</strong> <?= e($response['pet_name'] ?: 'Не указана') ?></p>
                <p class="mb-2"><strong>Порода:</strong> <?= e($response['breed_name'] ?: 'Не указана') ?></p>
                <p class="mb-2"><strong>Район:</strong> <?= e($response['district_name'] ?: 'Не указан') ?></p>
                <p class="mb-2"><strong>Адрес:</strong> <?= e($response['address'] ?: 'Не указан') ?></p>
                <p class="mb-0"><strong>Статус объявления:</strong> <?= e(format_status($response['ad_status'])) ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h4">Ваш отклик</h2>
                <p class="mb-2"><strong>ID отклика:</strong> #<?= (int)$response['response_id'] ?></p>
                <p class="mb-2"><strong>Дата:</strong> <?= e(date('d.m.Y H:i', strtotime($response['response_created_at']))) ?></p>
                <p class="mb-0"><strong>Статус отклика:</strong>
                    <?php $badgeClass = response_status_badge_class($response['response_status']); ?>
                    <span class="badge bg-<?= e($badgeClass) ?>">
                        <?= e(format_status($response['response_status'])) ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h4">Описание объявления</h2>
                <p class="mb-0"><?= nl2br(e($response['description'])) ?></p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4">Контакты автора объявления</h2>
                <p class="mb-2"><strong>Имя:</strong> <?= e($response['owner_name'] ?: 'Пользователь') ?></p>
                <p class="mb-2"><strong>Телефон:</strong> <?= e($response['owner_phone'] ?: 'Не указан') ?></p>
                <p class="mb-3"><strong>Email:</strong> <?= e($response['owner_email']) ?></p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="profile.php" class="btn btn-outline-secondary">Назад в профиль</a>
                    <a href="ad.php?id=<?= (int)$response['ad_id'] ?>" class="btn btn-primary">Открыть объявление</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
