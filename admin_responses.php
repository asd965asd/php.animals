<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    exit('Доступ запрещен. Только для администратора. <a href="login.php">Войти</a>');
}

$sql = "
    SELECT
        ad_responses.id AS response_id,
        ad_responses.created_at,
        ad_responses.status,
        users.email,
        users.username,
        ads.title,
        ads.status AS ad_status
    FROM ad_responses
    JOIN users ON ad_responses.user_id = users.id
    JOIN ads ON ad_responses.ad_id = ads.id
    ORDER BY ad_responses.id DESC
";

$stmt = $pdo->query($sql);
$responses = $stmt->fetchAll();

$pageTitle = 'Отклики на объявления';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Все отклики</h1>
    <a href="admin_panel.php" class="btn btn-outline-secondary">Назад в админку</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($responses): ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID отклика</th>
                            <th>Дата</th>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>Объявление</th>
                            <th>Статус отклика</th>
                            <th>Статус объявления</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($responses as $response): ?>
                        <tr>
                            <td><?= (int)$response['response_id'] ?></td>
                            <td><?= e($response['created_at']) ?></td>
                            <td><?= e($response['username'] ?: 'Пользователь') ?></td>
                            <td><?= e($response['email']) ?></td>
                            <td><?= e($response['title']) ?></td>
                            <td><?= e($response['status']) ?></td>
                            <td><?= e(format_status($response['ad_status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-light border mb-0">Откликов пока нет.</div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
