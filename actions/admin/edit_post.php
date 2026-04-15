<?php
// actions/admin/edit_post.php — редактирование объявления

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    header('Location: index.php?page=login');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    die('Объявление не найдено.');
}

$stmt = $pdo->prepare("SELECT id, type_animal, breed_animal, coloring_animal, district, description, image_url FROM posts WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    die('Объявление не найдено.');
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
        $img             = (string)$post['image_url'];

        // Если загружен новый файл — используем его вместо текущей ссылки
        if (isset($_FILES['image_file'])) {
            try {
                $uploadedPath = store_uploaded_image($_FILES['image_file'], 'posts');
                if (!empty($uploadedPath)) {
                    $oldImage = (string)$post['image_url'];
                    $img = $uploadedPath;

                    // Чистим старый локальный файл, чтобы не копился мусор
                    $prefix = 'uploads/posts/';
                    $hasPrefix = (substr($oldImage, 0, strlen($prefix)) === $prefix);
                    if ($oldImage !== '' && $oldImage !== $img && $hasPrefix) {
                        $oldAbs = __DIR__ . '/../../' . str_replace('/', DIRECTORY_SEPARATOR, $oldImage);
                        if (is_file($oldAbs)) {
                            @unlink($oldAbs);
                        }
                    }
                }
            } catch (RuntimeException $e) {
                $messageHtml = '<div class="alert alert-danger">' . h($e->getMessage()) . '</div>';
            }
        }

        if ($messageHtml !== '') {
            // ошибка загрузки уже записана выше
        } elseif ($type_animal === '' || $district === '') {
            $messageHtml = '<div class="alert alert-danger">Заполните вид животного и район!</div>';
        } else {
            $sql = "UPDATE posts
                    SET type_animal = :t,
                        breed_animal = :b,
                        coloring_animal = :c,
                        district = :r,
                        description = :d,
                        image_url = :i
                    WHERE id = :id";
            $upd = $pdo->prepare($sql);
            try {
                $upd->execute([
                    ':t' => $type_animal,
                    ':b' => $breed_animal,
                    ':c' => $coloring_animal,
                    ':r' => $district,
                    ':d' => $desc,
                    ':i' => $img,
                    ':id' => $id,
                ]);
                $messageHtml = '<div class="alert alert-success">Объявление обновлено!</div>';
                $post['type_animal'] = $type_animal;
                $post['breed_animal'] = $breed_animal;
                $post['coloring_animal'] = $coloring_animal;
                $post['district'] = $district;
                $post['description'] = $desc;
                $post['image_url'] = $img;
            } catch (PDOException $e) {
                log_exception($e, 'admin/edit_post: update');
                $messageHtml = '<div class="alert alert-danger">Ошибка базы данных. Попробуйте позже.</div>';
            }
        }
    }
}

