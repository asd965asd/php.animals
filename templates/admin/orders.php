<?php
// templates/admin/orders.php
require __DIR__ . '/../layout/header.php';
?>

<h2 class="mb-3">Все заявки</h2>
<a href="index.php?page=admin" class="btn btn-secondary mb-3">← В админку</a>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Дата</th>
            <th>Email</th>
            <th>Животное</th>
            <th>Район</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= (int)$order['order_id'] ?></td>
                <td><?= h($order['created_at']) ?></td>
                <td><?= h($order['email']) ?></td>
                <td><?= h($order['type_animal'] . ', ' . $order['breed_animal']) ?></td>
                <td><?= h($order['district']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

