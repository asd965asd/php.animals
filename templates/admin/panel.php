<?php
// templates/admin/panel.php
require __DIR__ . '/../layout/header.php';
?>

<div class="alert alert-success">
    <h1 class="h3">Панель Администратора</h1>
    <p class="mb-1">Добро пожаловать в админ-панель портала по поиску пропавших животных!</p>
    <p class="mb-0">Здесь вы управляете: объявлениями и заявками пользователей.</p>
</div>

<div class="mb-3">
    <a href="index.php?page=add_post" class="btn btn-success me-2">+ Добавить объявление</a>
    <a href="index.php?page=admin_orders" class="btn btn-outline-primary me-2">Заявки</a>
    <a href="index.php?page=admin_lost_requests" class="btn btn-outline-dark me-2">Заявки владельцев</a>
    <a href="index.php?page=admin_seeder" class="btn btn-outline-info me-2">Seeder</a>
    <a href="index.php?page=home" class="btn btn-outline-secondary">На главную</a>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

