<?php
// templates/layout/header.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? h($title) : 'Портал' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-light bg-light px-4 mb-4 shadow-sm">
    <a class="navbar-brand mb-0 h1 text-decoration-none" href="index.php?page=home">Поиск пропавших животных</a>
    <div>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="index.php?page=create_lost_request" class="btn btn-outline-warning btn-sm">+ Заявка о пропаже</a>
            <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="index.php?page=admin" class="btn btn-outline-danger btn-sm">Админка</a>
                <a href="index.php?page=add_post" class="btn btn-success btn-sm">+ Добавить объявление</a>
                <a href="index.php?page=admin_orders" class="btn btn-outline-secondary btn-sm">Заявки</a>
                <a href="index.php?page=admin_lost_requests" class="btn btn-outline-dark btn-sm">Заявки владельцев</a>
                <a href="index.php?page=admin_seeder" class="btn btn-outline-info btn-sm">Seeder</a>
            <?php endif; ?>
            <a href="index.php?page=profile" class="btn btn-outline-primary btn-sm">Мои заявки</a>
            <a href="index.php?page=logout" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <a href="index.php?page=login" class="btn btn-primary btn-sm">Войти</a>
            <a href="index.php?page=register" class="btn btn-outline-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">

