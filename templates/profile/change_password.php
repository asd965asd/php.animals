<?php
// templates/profile/change_password.php
require __DIR__ . '/../layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Смена пароля</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger"><?= h($errorMsg) ?></div>
                <?php endif; ?>
                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success"><?= h($successMsg) ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php?page=change_password" class="d-grid gap-3">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">

                    <div>
                        <label class="form-label">Старый пароль</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>

                    <div>
                        <label class="form-label">Новый пароль</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="form-text">Минимум 8 символов.</div>
                    </div>

                    <div>
                        <label class="form-label">Повтор нового пароля</label>
                        <input type="password" name="new_password_confirm" class="form-control" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="index.php?page=profile" class="btn btn-outline-secondary">Назад</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

