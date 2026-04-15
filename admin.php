<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();
$stats = [
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'ads' => (int)$pdo->query('SELECT COUNT(*) FROM ads')->fetchColumn(),
    'lost' => (int)$pdo->query("SELECT COUNT(*) FROM ads WHERE status='lost'")->fetchColumn(),
    'found' => (int)$pdo->query("SELECT COUNT(*) FROM ads WHERE status='found'")->fetchColumn(),
];
$recentAds = $pdo->query('SELECT ads.id, ads.title, ads.status, users.email, ads.created_at FROM ads JOIN users ON users.id = ads.user_id ORDER BY ads.id DESC LIMIT 15')->fetchAll();
$pageTitle = 'Админка';
require __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-4">Административная панель</h1>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted">Пользователи</div><div class="display-6"><?= $stats['users'] ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted">Объявления</div><div class="display-6"><?= $stats['ads'] ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted">Пропало</div><div class="display-6 text-danger"><?= $stats['lost'] ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm"><div class="card-body"><div class="text-muted">Найдено</div><div class="display-6 text-success"><?= $stats['found'] ?></div></div></div></div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5">Последние объявления</h2>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>ID</th><th>Заголовок</th><th>Пользователь</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($recentAds as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= e($row['title']) ?></td>
                        <td><?= e($row['email']) ?></td>
                        <td><?= e(format_status($row['status'])) ?></td>
                        <td><?= e($row['created_at']) ?></td>
                        <td><a href="ad.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
