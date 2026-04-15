<?php
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}
function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
function old(string $key, string $default = ''): string {
    return e($_POST[$key] ?? $default);
}
function ensure_csrf_token(): void {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
function csrf_input(): string {
    ensure_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}
function verify_csrf(): void {
    ensure_csrf_token();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Ошибка безопасности: неверный CSRF-токен.');
    }
}
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function is_logged_in(): bool {
    return current_user() !== null;
}
function is_admin(): bool {
    return is_logged_in() && (current_user()['role'] ?? '') === 'admin';
}
function require_login(): void {
    if (!is_logged_in()) {
        flash('error', 'Сначала войдите в систему.');
        redirect('login.php');
    }
}
function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        exit('Доступ запрещен. Только для администратора.');
    }
}
function upload_image(array $file): ?string {
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
function format_status(string $status): string {
    return match ($status) {
        'lost' => 'Пропало',
        'found' => 'Найдено',
        'closed' => 'Закрыто',
        default => $status,
    };
}
