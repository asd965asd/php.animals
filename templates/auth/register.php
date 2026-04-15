<?php
// templates/auth/register.php
require __DIR__ . '/../layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Регистрация</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger"><?= h($errorMsg) ?></div>
                <?php endif; ?>

                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success"><?= $successMsg ?></div>
                <?php else: ?>
                    <form method="POST" action="index.php?page=register">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label">Email адрес</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="index.php?page=login">Уже есть аккаунт? Войти</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

