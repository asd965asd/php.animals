<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Сайт';
}
$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Поиск животных</a>
        <div class="ms-auto d-flex gap-2 flex-wrap">
            <?php if (is_logged_in()): ?>
                <a href="profile.php" class="btn btn-outline-light btn-sm">Профиль</a>
                <a href="my_ads.php" class="btn btn-outline-info btn-sm">Мои объявления</a>
                <?php if (is_admin()): ?>
                    <a href="admin_panel.php" class="btn btn-warning btn-sm">Админка</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger btn-sm">Выход</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">Вход</a>
                <a href="register.php" class="btn btn-primary btn-sm">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= e($flashError) ?></div>
    <?php endif; ?>
