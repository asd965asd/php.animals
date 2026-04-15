<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();

$sql = "
    SELECT
        ad_responses.id AS response_id,
        ad_responses.created_at,
        ad_responses.status,
        ads.id AS ad_id,
        ads.title,
        ads.photo_path,
        ads.status AS ad_status,
        ads.pet_name
    FROM ad_responses
    JOIN ads ON ad_responses.ad_id = ads.id
    WHERE ad_responses.user_id = ?
    ORDER BY ad_responses.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$myResponses = $stmt->fetchAll();

$pageTitle = 'Личный кабинет';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h1 class="h3 mb-3">Профиль</h1>
                <p class="mb-2"><strong>Имя:</strong> <?= e($user['username'] ?: 'Пользователь') ?></p>
                <p class="mb-2"><strong>Email:</strong> <?= e($user['email']) ?></p>
                <p class="mb-2"><strong>Телефон:</strong> <?= e($user['phone'] ?: 'Не указан') ?></p>
                <p class="mb-3"><strong>Роль:</strong> <?= e($user['role']) ?></p>
                <div class="d-grid gap-2">
                    <a href="change_password.php" class="btn btn-primary">Сменить пароль</a>
                    <a href="my_ads.php" class="btn btn-outline-secondary">Мои объявления</a>
                    <?php if (is_admin()): ?>
                        <a href="admin_panel.php" class="btn btn-warning">Админка</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h4 mb-0">Мои отклики</h2>
            </div>
            <div class="card-body">
                <?php if ($myResponses): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID отклика</th>
                                    <th>Дата</th>
                                    <th>Объявление</th>
                                    <th>Статус отклика</th>
                                    <th>Статус объявления</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($myResponses as $response): ?>
                                <tr>
                                    <td>#<?= (int)$response['response_id'] ?></td>
                                    <td><?= e(date('d.m.Y H:i', strtotime($response['created_at']))) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($response['title']) ?></div>
                                        <div class="small text-muted"><?= e($response['pet_name'] ?: 'Без клички') ?></div>
                                    </td>
                                    <td>
                                        <?php $badgeClass = response_status_badge_class($response['status']); ?>
                                        <span class="badge bg-<?= e($badgeClass) ?>">
                                            <?= e(format_status($response['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= e(format_status($response['ad_status'])) ?></td>
                                    <td>
                                        <a href="response_details.php?id=<?= (int)$response['response_id'] ?>" class="btn btn-outline-primary btn-sm">Подробнее</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <h3 class="h5 text-muted">Вы еще не откликались на объявления.</h3>
                        <a href="index.php" class="btn btn-primary mt-3">Перейти к объявлениям</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
