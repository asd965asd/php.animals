<?php
// actions/auth/login.php — логика входа

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $pass  = isset($_POST['password']) ? (string)$_POST['password'] : '';

    $stmt = $pdo->prepare("SELECT id, role, password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_role'] = (string)$user['role'];

        header('Location: index.php?page=home');
        exit;
    }

    $errorMsg = 'Неверный логин или пароль';
}

