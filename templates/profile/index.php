<?php
// templates/profile/index.php
require __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Личный кабинет</h2>
    <div class="d-flex align-items-center gap-2">
        <img src="<?= h($avatarUrl) ?>" alt="avatar" width="34" height="34" class="rounded-circle" style="object-fit:cover;">
        <span class="text-muted small">Роль: <b><?= h($_SESSION['user_role'] ?? 'user') ?></b></span>
        <a href="index.php?page=change_password" class="btn btn-outline-secondary btn-sm">Сменить пароль</a>
    </div>
</div>

<?php if (!empty($profileError)): ?>
    <div class="alert alert-danger"><?= h($profileError) ?></div>
<?php endif; ?>

<div class="card mb-4 p-3 bg-light">
    <div class="row g-3 align-items-center">
        <div class="col-md-8">
            <div class="fw-bold mb-1">Аватар профиля</div>
            <div class="text-muted small">Загружается в <code>uploads/avatars</code> и путь сохраняется в <code>users.avatar_url</code>.</div>
        </div>
        <div class="col-md-4">
            <form action="upload.php" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="target" value="avatar">
                <input type="file" name="file" class="form-control form-control-sm" required>
                <button type="submit" class="btn btn-success btn-sm">Загрузить</button>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">Мои заявки по объявлениям</h4>
    </div>
    <div class="card-body">
        <?php if (!empty($my_orders)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>№</th>
                        <th>Дата</th>
                        <th>Животное</th>
                        <th>Район</th>
                        <th>Статус</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($my_orders as $order): ?>
                        <tr>
                            <td>#<?= (int)$order['order_id'] ?></td>
                            <td><?= h(date('d.m.Y H:i', strtotime($order['created_at']))) ?></td>
                            <td><b><?= h($order['type_animal']) ?></b>, <?= h($order['breed_animal']) ?></td>
                            <td><?= h($order['district']) ?></td>
                            <td><?= h($order['status']) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a class="btn btn-outline-primary btn-sm" href="index.php?page=order_details&id=<?= (int)$order['order_id'] ?>">Детали</a>
                                    <?php if (($order['status'] ?? '') === 'new'): ?>
                                        <form method="POST" action="index.php?page=cancel_order" onsubmit="return confirm('Отменить заявку?');">
                                            <input type="hidden" name="id" value="<?= (int)$order['order_id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Отменить</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <h5 class="text-muted">Вы ещё не откликались на объявления.</h5>
                <a href="index.php?page=home" class="btn btn-primary mt-2">Перейти к объявлениям</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

