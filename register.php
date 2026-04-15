<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $passwordConfirm === '') {
        $error = 'Заполните обязательные поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Пароли не совпадают.';
    } elseif (mb_strlen($password) < 6) {
        $error = 'Пароль должен быть не короче 6 символов.';
    } else {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->execute([$email]);
        $exists = $checkStmt->fetch();

        if ($exists) {
            $error = 'Пользователь с таким email уже существует.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, username, phone, role, created_at)
                VALUES (?, ?, ?, ?, 'client', NOW())
            ");

            try {
                $stmt->execute([$email, $hash, $username, $phone]);
                flash('success', 'Регистрация прошла успешно. Теперь войдите в систему.');
                redirect('login.php');
            } catch (PDOException $e) {
                $error = 'Ошибка при регистрации: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Регистрация';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Регистрация</h1>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_input() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Имя</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            value="<?= e($_POST['username'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= e($_POST['email'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control"
                            value="<?= e($_POST['phone'] ?? '') ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Повторите пароль</label>
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            class="form-control"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>

                <div class="mt-3 text-center">
                    <a href="login.php">Уже есть аккаунт? Войти</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>