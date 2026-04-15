<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$pageTitle = 'Админ-панель';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Админ-панель</h1>
                <p class="text-muted mb-4">Добро пожаловать, администратор.</p>

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title">Добавить объявление</h5>
                                <p class="card-text">Создание нового объявления о пропавшем животном.</p>
                                <a href="create_ad.php" class="btn btn-primary">Перейти</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title">Управление объявлениями</h5>
                                <p class="card-text">Редактирование, поиск и снятие с публикации.</p>
                                <a href="admin_ads.php" class="btn btn-warning">Открыть</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title">Управление районами</h5>
                                <p class="card-text">Добавление районов для формы объявления.</p>
                                <a href="admin_districts.php" class="btn btn-dark">Открыть</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title">Управление породами</h5>
                                <p class="card-text">Добавление пород для формы объявления.</p>
                                <a href="admin_breeds.php" class="btn btn-success">Открыть</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title">Все отклики</h5>
                                <p class="card-text">Просмотр откликов пользователей на объявления.</p>
                                <a href="admin_responses.php" class="btn btn-outline-primary">Открыть</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title">Seeder и бэкап</h5>
                                <p class="card-text">Генерация тестовых данных.</p>
                                <a href="admin_seeder.php" class="btn btn-outline-secondary">Открыть</a>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2 flex-wrap">
                    <a href="profile.php" class="btn btn-outline-dark">Профиль</a>
                    <a href="index.php" class="btn btn-outline-secondary">На главную</a>
                    <a href="logout.php" class="btn btn-danger">Выйти</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>