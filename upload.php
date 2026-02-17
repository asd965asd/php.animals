<?php
// upload.php — безопасная загрузка файлов.
// Режимы:
// - target=post_image  (только админ): загрузка фото для объявления (возвращаем путь)
// - target=avatar      (любой пользователь): загрузка аватарки (сохраняем путь в users.avatar_url)

session_start();
require 'db.php';

header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Доступ запрещен. <a href='login.php'>Войти</a>");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
) {
    http_response_code(400);
    die("Ошибка безопасности (CSRF). <a href='index.php'>Вернуться</a>");
}

$target = isset($_POST['target']) ? (string)$_POST['target'] : 'post_image';
if ($target !== 'post_image' && $target !== 'avatar') {
    http_response_code(400);
    die("Неверный режим загрузки. <a href='index.php'>Вернуться</a>");
}

// Для загрузки фото объявления — только админ
if ($target === 'post_image' && (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    die("Доступ запрещен. Только администратор. <a href='index.php'>Вернуться</a>");
}

if (empty($_FILES['file']) || !isset($_FILES['file']['error'])) {
    die("Файл не получен. <a href='index.php'>Вернуться</a>");
}

if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Ошибка загрузки файла (код: " . (int)$_FILES['file']['error'] . "). <a href='index.php'>Вернуться</a>");
}

// Ограничения
$maxBytes = 5 * 1024 * 1024; // 5 MB
if (!empty($_FILES['file']['size']) && (int)$_FILES['file']['size'] > $maxBytes) {
    die("Файл слишком большой (макс. 5MB). <a href='index.php'>Вернуться</a>");
}

$tmpPath = $_FILES['file']['tmp_name'];

// MIME-проверка (важнее, чем расширение)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmpPath);
$allowed = array(
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
);

if (!isset($allowed[$mime])) {
    die("Разрешены только изображения (jpg/png/webp/gif). <a href='index.php'>Вернуться</a>");
}

// Доп. проверка: файл должен реально быть изображением
if (@getimagesize($tmpPath) === false) {
    die("Файл не является изображением. <a href='index.php'>Вернуться</a>");
}

$ext = $allowed[$mime];
$subDir = ($target === 'avatar') ? 'avatars' : 'posts';
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subDir;
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        die("Не удалось создать папку uploads/. <a href='index.php'>Вернуться</a>");
    }
}

// Генерируем безопасное имя
$name = bin2hex(random_bytes(16)) . '.' . $ext;
$destAbs = $uploadDir . DIRECTORY_SEPARATOR . $name;
$destRel = 'uploads/' . $subDir . '/' . $name;

if (!move_uploaded_file($tmpPath, $destAbs)) {
    die("Не удалось сохранить файл. <a href='index.php'>Вернуться</a>");
}

// Если это аватар — сохраняем путь в БД
if ($target === 'avatar') {
    try {
        // Читаем старый аватар, чтобы убрать мусор в uploads/avatars
        $oldStmt = $pdo->prepare("SELECT avatar_url FROM users WHERE id = ? LIMIT 1");
        $oldStmt->execute([(int)$_SESSION['user_id']]);
        $oldRow = $oldStmt->fetch();
        $oldAvatar = $oldRow && !empty($oldRow['avatar_url']) ? (string)$oldRow['avatar_url'] : '';

        $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
        $stmt->execute([$destRel, (int)$_SESSION['user_id']]);

        // Удаляем старый файл, если он был локальный и отличается от нового
        $prefix = 'uploads/avatars/';
        $hasPrefix = (substr($oldAvatar, 0, strlen($prefix)) === $prefix);
        if ($oldAvatar !== '' && $oldAvatar !== $destRel && $hasPrefix) {
            $absOld = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $oldAvatar);
            if (is_file($absOld)) {
                @unlink($absOld);
            }
        }
    } catch (PDOException $e) {
        // Файл уже сохранён, но поле в БД не обновилось (скорее всего нет колонки avatar_url)
        die(
            "Файл загружен, но не удалось записать в БД (проверьте колонку users.avatar_url). " .
            "<br>Путь: <code>" . h($destRel) . "</code>" .
            "<br><a href='profile.php'>Вернуться</a>"
        );
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка фото</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">Файл загружен</h4>
            <?php if ($target === 'post_image'): ?>
                <p class="mb-2">Скопируйте путь и вставьте в поле <b>«Ссылка на фото»</b>:</p>
            <?php else: ?>
                <p class="mb-2">Аватар обновлён. Путь сохранён в базе данных.</p>
            <?php endif; ?>
            <div class="alert alert-success">
                <code><?= h($destRel) ?></code>
            </div>
            <?php if ($target === 'post_image'): ?>
                <a class="btn btn-primary" href="add_item.php">Вернуться к добавлению объявления</a>
            <?php else: ?>
                <a class="btn btn-primary" href="profile.php">Вернуться в профиль</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

