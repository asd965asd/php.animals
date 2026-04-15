<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configPath = __DIR__ . '/../config/config.php';

if (!file_exists($configPath)) {
    die('Ошибка: файл config/config.php не найден');
}

$config = require $configPath;

if (!is_array($config)) {
    die('Ошибка: config/config.php должен возвращать массив');
}

$host = isset($config['host']) ? $config['host'] : '';
$db   = isset($config['db']) ? $config['db'] : '';
$user = isset($config['user']) ? $config['user'] : '';
$pass = isset($config['pass']) ? $config['pass'] : '';
$charset = isset($config['charset']) ? $config['charset'] : 'utf8mb4';

if ($host === '' || $db === '' || $user === '') {
    die('Ошибка: не заполнены host/db/user в config/config.php');
}

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function is_logged_in()
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_admin()
{
    return is_logged_in() && (current_user()['role'] ?? '') === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        flash('error', 'Сначала войдите в систему.');
        header('Location: login.php');
        exit;
    }
}

function flash($key, $message = null)
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    if (isset($_SESSION['_flash'][$key])) {
        $msg = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    return null;
}

function csrf_token()
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function refresh_csrf_token()
{
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf_token'];
}

function csrf_input()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $sessionToken = isset($_SESSION['_csrf_token']) ? $_SESSION['_csrf_token'] : '';
    $formToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if ($sessionToken === '' || $formToken === '' || !hash_equals($sessionToken, $formToken)) {
        http_response_code(403);
        die('Ошибка безопасности: неверный CSRF-токен.');
    }
}

function format_status($status)
{
    if ($status === 'lost') return 'Пропало';
    if ($status === 'found') return 'Найдено';
    if ($status === 'closed') return 'Закрыто';
    if ($status === 'new') return 'Новый';
    if ($status === 'processing') return 'В обработке';
    if ($status === 'done') return 'Завершено';
    return (string)$status;
}

function response_status_badge_class($status)
{
    if ($status === 'new') return 'primary';
    if ($status === 'processing') return 'warning text-dark';
    if ($status === 'done') return 'success';
    return 'secondary';
}

function old($key, $default = '')
{
    return e($_POST[$key] ?? $default);
}

function upload_image($file)
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Ошибка загрузки файла.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Файл слишком большой. Максимум 5 МБ.');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Разрешены только JPG, PNG, GIF, WEBP.');
    }
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $filename = uniqid('pet_', true) . '.' . $allowed[$mime];
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }
    return 'uploads/' . $filename;
}


function require_admin()
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Доступ запрещен. У вас нет прав администратора.');
    }
}
