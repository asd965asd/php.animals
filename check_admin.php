<?php
// check_admin.php — Скрипт защиты (Middleware)

// 1. Включаем доступ к сессии
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2. Проверяем два условия:
//    А. Пользователь вообще вошел? (есть ли user_id)
//    Б. Его роль — это 'admin'?
if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    
    // Если условия не выполнены — останавливаем работу жестко
    // Можно сделать красивее (редирект), но для безопасности надежнее так:
    http_response_code(403);
    die("ДОСТУП ЗАПРЕЩЕН. У вас нет прав администратора. <a href='login.php'>Войти</a>");
}

// Если код идет дальше — значит, это Админ.

// Генерируем CSRF-токен, если его ещё нет
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        $_SESSION['csrf_token'] = bin2hex(md5(uniqid(mt_rand(), true)));
    }
}
?>