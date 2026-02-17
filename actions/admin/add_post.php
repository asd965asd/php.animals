<?php
// actions/admin/add_post.php — добавление объявления

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    header('Location: index.php?page=login');
    exit;
}

$messageHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
    ) {
        $messageHtml = '<div class="alert alert-danger">Ошибка безопасности (CSRF). Обновите страницу и попробуйте снова.</div>';
    } else {
        $type_animal     = trim((string)($_POST['type_animal'] ?? ''));
        $breed_animal    = trim((string)($_POST['breed_animal'] ?? ''));
        $coloring_animal = trim((string)($_POST['coloring_animal'] ?? ''));
        $district        = trim((string)($_POST['district'] ?? ''));
        $desc            = trim((string)($_POST['description'] ?? ''));
        $img             = '';

        if (isset($_FILES['image_file'])) {
            try {
                $uploadedPath = store_uploaded_image($_FILES['image_file'], 'posts');
                if (!empty($uploadedPath)) {
                    $img = $uploadedPath;
                }
            } catch (RuntimeException $e) {
                $messageHtml = '<div class="alert alert-danger">' . h($e->getMessage()) . '</div>';
            }
        }

        if ($messageHtml !== '') {
            // ошибка загрузки уже записана выше
        } elseif ($type_animal === '' || $district === '' || $img === '') {
            $messageHtml = '<div class="alert alert-danger">Заполните вид/район и загрузите фото.</div>';
        } else {
            $sql = "INSERT INTO posts (type_animal, breed_animal, coloring_animal, district, description, image_url)
                    VALUES (:t, :b, :c, :r, :d, :i)";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':t' => $type_animal,
                    ':b' => $breed_animal,
                    ':c' => $coloring_animal,
                    ':r' => $district,
                    ':d' => $desc,
                    ':i' => $img,
                ]);
                $messageHtml = '<div class="alert alert-success">Объявление успешно добавлено!</div>';
            } catch (PDOException $e) {
                log_exception($e, 'admin/add_post: insert');
                $messageHtml = '<div class="alert alert-danger">Ошибка базы данных. Попробуйте позже.</div>';
            }
        }
    }
}

