<?php
// templates/auth/login.php
require __DIR__ . '/../layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Вход на портал</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger"><?= h($errorMsg) ?></div>
                <?php endif; ?>
                <form method="POST" action="index.php?page=login">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="index.php?page=register">Нет аккаунта? Зарегистрироваться</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>

