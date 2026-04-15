<?php
/**
 * Одноразовый скрипт: создать пользователя-админа.
 * После создания админа — УДАЛИТЕ этот файл с сервера!
 */
// Безопасность: запрещаем запуск из браузера.
// Запускайте только из консоли:
// php create_admin.php "admin@mail.ru" "StrongPassword123"

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require 'db.php';

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Usage: php create_admin.php \"email\" \"password\"\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Error: invalid email\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, 'admin')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'admin'";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email, ':hash' => $hash]);
    echo "OK: admin user ensured for {$email}\n";
} catch (PDOException $e) {
    fwrite(STDERR, "DB error: " . $e->getMessage() . "\n");
    exit(1);
}
