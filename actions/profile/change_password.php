<?php
// actions/profile/change_password.php — смена пароля

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
    ) {
        $errorMsg = 'Ошибка безопасности: неверный CSRF-токен.';
    } else {
        $old = (string)($_POST['old_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $new2 = (string)($_POST['new_password_confirm'] ?? '');

        if ($old === '' || $new === '' || $new2 === '') {
            $errorMsg = 'Заполните все поля.';
        } elseif (strlen($new) < 8) {
            $errorMsg = 'Новый пароль должен быть не короче 8 символов.';
        } elseif ($new !== $new2) {
            $errorMsg = 'Новые пароли не совпадают.';
        } else {
            $user_id = (int)$_SESSION['user_id'];
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch();

            if (!$row || empty($row['password_hash'])) {
                $errorMsg = 'Пользователь не найден.';
            } elseif (!password_verify($old, $row['password_hash'])) {
                $errorMsg = 'Старый пароль неверный.';
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $upd->execute([$newHash, $user_id]);
                session_regenerate_id(true);
                $successMsg = 'Пароль успешно изменён.';
            }
        }
    }
}

