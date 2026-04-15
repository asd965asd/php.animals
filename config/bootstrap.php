<?php
// config/bootstrap.php — единая инициализация приложения

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/Database.php';

$config = require __DIR__ . '/config.php';

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', !empty($config['debug']));
}

if (!function_exists('app_log')) {
    function app_log($message) {
        error_log('[app] ' . $message);
    }
}

if (!function_exists('log_exception')) {
    function log_exception($e, $context = '') {
        $ctx = $context !== '' ? ($context . ' | ') : '';
        $msg = $ctx . get_class($e) . ': ' . $e->getMessage() .
            ' in ' . $e->getFile() . ':' . $e->getLine();
        app_log($msg);
    }
}

try {
    $pdo = Database::getConnection();
    $GLOBALS['pdo'] = $pdo;
} catch (Exception $e) {
    log_exception($e, 'bootstrap: Database::getConnection');
    http_response_code(500);
    if (APP_DEBUG) {
        exit('DB error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
    exit('Ошибка подключения к базе данных. Попробуйте позже.');
}

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        $_SESSION['csrf_token'] = bin2hex(md5(uniqid(mt_rand(), true)));
    }
}

if (!function_exists('render')) {
    function render($templatePath, array $data = array()) {
        extract($data, EXTR_SKIP);
        require $templatePath;
    }
}

if (!function_exists('store_uploaded_image')) {
    function store_uploaded_image($file, $subDir = 'posts') {
        if (!is_array($file) || !isset($file['error'])) {
            throw new RuntimeException('Файл не получен.');
        }

        if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Ошибка загрузки файла (код: ' . (int)$file['error'] . ').');
        }

        $maxBytes = 5 * 1024 * 1024;
        if (!empty($file['size']) && (int)$file['size'] > $maxBytes) {
            throw new RuntimeException('Файл слишком большой (макс. 5MB).');
        }

        $tmpPath = $file['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);
        $allowed = array(
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        );

        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Разрешены только изображения (jpg/png/webp/gif).');
        }

        if (@getimagesize($tmpPath) === false) {
            throw new RuntimeException('Файл не является изображением.');
        }

        $ext = $allowed[$mime];
        $subDir = trim((string)$subDir, '/\\');
        $uploadDir = __DIR__ . '/../uploads/' . $subDir;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Не удалось создать папку для загрузки.');
        }

        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $destAbs = $uploadDir . DIRECTORY_SEPARATOR . $name;
        $destRel = 'uploads/' . $subDir . '/' . $name;

        if (!move_uploaded_file($tmpPath, $destAbs)) {
            throw new RuntimeException('Не удалось сохранить файл.');
        }

        return $destRel;
    }
}

