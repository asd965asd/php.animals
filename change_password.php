<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$error = '';
$success = '';

if (is_post()) {
    verify_csrf();

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'Заполните все поля.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Новый пароль и подтверждение не совпадают.';
    } elseif (mb_strlen($newPassword) < 8) {
        $error = 'Новый пароль должен быть не короче 8 символов.';
    } else {
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
            $error = 'Текущий пароль введен неверно.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $update->execute([$newHash, $user['id']]);
            refresh_csrf_token();
            $success = 'Пароль успешно изменен.';
        }
    }
}

$pageTitle = 'Смена пароля';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Смена пароля</h1>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_input() ?>

                    <div class="mb-3">
                        <label class="form-label">Текущий пароль</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Новый пароль</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Повтор нового пароля</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="profile.php" class="btn btn-outline-secondary">Назад</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
