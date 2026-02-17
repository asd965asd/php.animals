<?php
// actions/auth/register.php — логика регистрации (через модель User)

require_once __DIR__ . '/../../src/Models/User.php';

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
    ) {
        $errorMsg = 'Ошибка безопасности (CSRF). Обновите страницу и попробуйте снова.';
    } else {
        $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $pass = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $passConfirm = isset($_POST['password_confirm']) ? (string)$_POST['password_confirm'] : '';

        if ($email === '' || $pass === '') {
            $errorMsg = 'Заполните все поля!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Некорректный формат Email!';
        } elseif ($pass !== $passConfirm) {
            $errorMsg = 'Пароли не совпадают!';
        } else {
            $userModel = new User();
            if ($userModel->findByEmail($email)) {
                $errorMsg = 'Такой email уже зарегистрирован.';
            } else {
                try {
                    if ($userModel->create($email, $pass)) {
                        $successMsg = "Регистрация успешна! <a href='index.php?page=login'>Войти</a>";
                    } else {
                        $errorMsg = 'Не удалось создать пользователя.';
                    }
                } catch (Throwable $e) {
                    log_exception($e, 'register: create user');
                    $errorMsg = 'Ошибка базы данных. Попробуйте позже.';
                }
            }
        }
    }
}

