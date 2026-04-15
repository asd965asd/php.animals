<?php
// actions/requests/create.php — заявка владельца о пропаже (с фото)

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$requestMessageHtml = '';

// таблица для заявок владельцев (создаём один раз при первом заходе)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS lost_pet_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type_animal VARCHAR(100) NOT NULL,
        breed_animal VARCHAR(100) DEFAULT NULL,
        coloring_animal VARCHAR(100) DEFAULT NULL,
        district VARCHAR(120) NOT NULL,
        description TEXT,
        image_url VARCHAR(255) NOT NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
    ) {
        $requestMessageHtml = '<div class="alert alert-danger">Ошибка безопасности (CSRF).</div>';
    } else {
        $type_animal     = trim((string)($_POST['type_animal'] ?? ''));
        $breed_animal    = trim((string)($_POST['breed_animal'] ?? ''));
        $coloring_animal = trim((string)($_POST['coloring_animal'] ?? ''));
        $district        = trim((string)($_POST['district'] ?? ''));
        $description     = trim((string)($_POST['description'] ?? ''));

        $img = '';
        if (isset($_FILES['image_file'])) {
            try {
                $img = (string)store_uploaded_image($_FILES['image_file'], 'requests');
            } catch (RuntimeException $e) {
                $requestMessageHtml = '<div class="alert alert-danger">' . h($e->getMessage()) . '</div>';
            }
        }

        if ($requestMessageHtml !== '') {
            // уже есть ошибка
        } elseif ($type_animal === '' || $district === '' || $img === '') {
            $requestMessageHtml = '<div class="alert alert-danger">Заполните вид/район и загрузите фото.</div>';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO lost_pet_requests 
                (user_id, type_animal, breed_animal, coloring_animal, district, description, image_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            try {
                $stmt->execute([
                    (int)$_SESSION['user_id'],
                    $type_animal,
                    $breed_animal,
                    $coloring_animal,
                    $district,
                    $description,
                    $img
                ]);
                $requestMessageHtml = '<div class="alert alert-success">Заявка отправлена. После модерации появится в объявлениях.</div>';
            } catch (PDOException $e) {
                log_exception($e, 'requests/create: insert');
                $requestMessageHtml = '<div class="alert alert-danger">Ошибка базы данных. Попробуйте позже.</div>';
            }
        }
    }
}

