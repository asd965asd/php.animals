<?php

require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    public function register()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Подтягиваем db.php, чтобы были $pdo / h() / log_exception()
        require_once __DIR__ . '/../../db.php';

        // CSRF-токен для формы регистрации
        if (empty($_SESSION['csrf_token'])) {
            if (function_exists('random_bytes')) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            } else {
                $_SESSION['csrf_token'] = bin2hex(md5(uniqid(mt_rand(), true)));
            }
        }

        $errorMsg = '';
        $successMsg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF-проверка
            if (
                empty($_POST['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])
            ) {
                $errorMsg = "Ошибка безопасности (CSRF). Обновите страницу и попробуйте снова.";
            } else {
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $pass = isset($_POST['password']) ? $_POST['password'] : '';
            $passConfirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

            if ($email === '' || $pass === '') {
                $errorMsg = "Заполните все поля!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "Некорректный формат Email!";
            } elseif ($pass !== $passConfirm) {
                $errorMsg = "Пароли не совпадают!";
            } else {
                $userModel = new User();

                // Проверяем дубликат email
                if ($userModel->findByEmail($email)) {
                    $errorMsg = "Такой email уже зарегистрирован.";
                } else {
                    try {
                        if ($userModel->create($email, $pass)) {
                            $successMsg = "Регистрация успешна! <a href='login.php'>Войти</a>";
                        } else {
                            $errorMsg = "Не удалось создать пользователя.";
                        }
                    } catch (Exception $e) {
                        log_exception($e, 'register: create user');
                        $errorMsg = "Ошибка базы данных. Попробуйте позже.";
                    }
                }
            }
            }
        }

        // Подключаем шаблон (View)
        // В нём используются переменные $errorMsg и $successMsg
        require __DIR__ . '/../../templates/register.php';
    }
}

