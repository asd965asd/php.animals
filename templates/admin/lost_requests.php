<?php
require __DIR__ . '/../layout/header.php';
?>

<h2 class="mb-3">Заявки владельцев о пропаже</h2>
<a href="index.php?page=admin" class="btn btn-secondary mb-3">← В админку</a>

<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Дата</th>
            <th>Пользователь</th>
            <th>Животное</th>
            <th>Район</th>
            <th>Фото</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lostRequests as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= h($r['created_at']) ?></td>
                <td><?= h($r['email'] ?: ('user#' . (int)$r['user_id'])) ?></td>
                <td><?= h($r['type_animal'] . ', ' . $r['breed_animal']) ?></td>
                <td><?= h($r['district']) ?></td>
                <td>
                    <img src="<?= h($r['image_url']) ?>" alt="pet" style="max-width:120px;max-height:90px;object-fit:cover;" class="rounded border">
                </td>
                <td><?= h($r['status']) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                        <div class="d-flex gap-2">
                            <form method="POST" action="index.php?page=admin_lost_request_decide">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="decision" value="approve">
                                <button type="submit" class="btn btn-success btn-sm">Одобрить</button>
                            </form>
                            <form method="POST" action="index.php?page=admin_lost_request_decide">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="decision" value="reject">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Отклонить</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

