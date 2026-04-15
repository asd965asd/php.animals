<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Заполните все поля.';
    } else {
        $stmt = $pdo->prepare("SELECT id, email, username, role, phone, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            refresh_csrf_token();

            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role'],
                'phone' => $user['phone'],
            ];

            flash('success', 'Вы успешно вошли в систему.');
            redirect('profile.php');
        } else {
            $error = 'Неверный логин или пароль.';
        }
    }
}

$pageTitle = 'Вход';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Вход</h1>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_input() ?>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>

                <div class="mt-3 text-center">
                    <a href="register.php">Нет аккаунта? Зарегистрироваться</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
