<?php
// templates/profile/order_details.php
require __DIR__ . '/../layout/header.php';
?>

<a class="btn btn-secondary mb-3" href="index.php?page=profile">← В мои заявки</a>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Заявка #<?= (int)$order['order_id'] ?></h4>
            <div class="text-muted small">Создана: <?= h(date('d.m.Y H:i', strtotime($order['created_at']))) ?></div>
        </div>
        <span class="badge bg-secondary"><?= h($order['status']) ?></span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <img src="<?= h($img) ?>" class="img-fluid rounded" alt="Фото животного">
            </div>
            <div class="col-md-6">
                <div class="mb-2"><b>Животное:</b> <?= h($order['type_animal']) ?>, <?= h($order['breed_animal']) ?></div>
                <div class="mb-2"><b>Окрас:</b> <?= h($order['coloring_animal']) ?></div>
                <div class="mb-2"><b>Район:</b> <?= h($order['district']) ?></div>
                <div class="mb-2"><b>Описание:</b><br><?= nl2br(h($order['description'])) ?></div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <a href="index.php?page=home" class="btn btn-primary">К объявлениям</a>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

