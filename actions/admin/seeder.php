<?php
// actions/admin/seeder.php — генератор данных + CSV backup

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    http_response_code(403);
    header('Location: index.php?page=login');
    exit;
}

$seedMessage = '';
$defaultCount = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])
    ) {
        $seedMessage = '<div class="alert alert-danger">CSRF error.</div>';
    } else {
        $count = (int)($_POST['count'] ?? $defaultCount);
        if ($count < 1) $count = 1;
        if ($count > 500) $count = 500;

        $rows = $pdo->query("SELECT id, type_animal, breed_animal, coloring_animal, district, description, image_url FROM posts")->fetchAll();
        if (empty($rows)) {
            $seedMessage = '<div class="alert alert-warning">Таблица posts пуста. Добавьте хотя бы одно объявление.</div>';
        } else {
            // backup csv
            $exportDir = __DIR__ . '/../../exports';
            if (!is_dir($exportDir)) {
                @mkdir($exportDir, 0755, true);
            }
            $filename = $exportDir . '/posts_' . date('Y-m-d_H-i-s') . '.csv';
            $fp = @fopen($filename, 'w');
            if ($fp) {
                fputcsv($fp, array_keys($rows[0]));
                foreach ($rows as $r) {
                    fputcsv($fp, $r);
                }
                fclose($fp);
            }

            $tpl = $rows[array_rand($rows)];
            $ins = $pdo->prepare("
                INSERT INTO posts (type_animal, breed_animal, coloring_animal, district, description, image_url)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $created = 0;
            for ($i = 1; $i <= $count; $i++) {
                $ok = $ins->execute([
                    $tpl['type_animal'],
                    trim((string)$tpl['breed_animal']) . ' #' . mt_rand(100, 9999),
                    $tpl['coloring_animal'],
                    $tpl['district'],
                    trim((string)$tpl['description']) . ' (seed ' . $i . ')',
                    $tpl['image_url'],
                ]);
                if ($ok) $created++;
            }

            $seedMessage = '<div class="alert alert-success">Сгенерировано записей: ' . (int)$created . '. CSV backup: exports/</div>';
        }
    }
}

