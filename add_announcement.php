<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user = current_user();

if (($user['role'] ?? '') !== 'admin') {
    die('Доступ запрещен.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'lost');

    if ($title === '' || $description === '') {
        $error = 'Заполните все обязательные поля.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO announcements (title, description, status, user_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$title, $description, $status, $user['id']]);
            $success = 'Объявление успешно добавлено.';
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Добавить объявление';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Добавить объявление</h1>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_input() ?>

                    <div class="mb-3">
                        <label class="form-label">Заголовок</label>
                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="<?= e($_POST['title'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="5"
                            required
                        ><?= e($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="lost">Пропало</option>
                            <option value="found">Найдено</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="admin_panel.php" class="btn btn-outline-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>