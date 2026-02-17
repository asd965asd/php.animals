<?php
// hash_gen.php — генерация хеша пароля (для разработки/инициализации)
// Безопасность: запрещаем запуск из браузера.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

// Использование:
// php hash_gen.php "пароль"
$password = $argv[1] ?? '';
if ($password === '') {
    fwrite(STDERR, "Usage: php hash_gen.php \"password\"\n");
    exit(1);
}

echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;