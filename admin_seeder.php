<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$message = '';

$tables = [];
$stmt = $pdo->query('SHOW TABLES');
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$allowedTables = array_values(array_intersect($tables, ['ads', 'breeds', 'districts']));

if (is_post()) {
    verify_csrf();

    $tableName = $_POST['table_name'] ?? 'ads';
    $count = max(1, min(100, (int)($_POST['count'] ?? 10)));

    if (!in_array($tableName, $allowedTables, true)) {
        $message = 'Ошибка: Таблица не найдена.';
    } else {
        $exportDir = __DIR__ . '/exports';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0775, true);
        }

        $filename = $exportDir . '/' . $tableName . '_' . date('Y-m-d_H-i-s') . '.csv';
        $fp = fopen($filename, 'w');

        $stmt = $pdo->query("SELECT * FROM `$tableName`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            $message = 'Таблица пуста. Сначала создайте хотя бы одну запись вручную.';
        } else {
            fputcsv($fp, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);

            if ($tableName !== 'ads') {
                $message = 'Бэкап сохранен в exports. Генерация доступна только для таблицы ads.';
            } else {
                $inserted = 0;
                $templateRows = array_values(array_filter($rows, function ($row) {
                    return isset($row['status']) ? $row['status'] !== 'closed' : true;
                }));
                if (!$templateRows) {
                    $templateRows = $rows;
                }

                for ($i = 0; $i < $count; $i++) {
                    $template = $templateRows[array_rand($templateRows)];
                    $title = $template['title'] . ' #' . mt_rand(1000, 9999);
                    $petName = ($template['pet_name'] ?: 'Питомец') . '-' . mt_rand(10, 99);
                    $description = $template['description'] . ' (тестовая копия ' . mt_rand(100, 999) . ')';
                    $status = in_array($template['status'], ['lost', 'found'], true) ? $template['status'] : 'lost';
                    $address = $template['address'] ? $template['address'] . ', секция ' . mt_rand(1, 9) : null;
                    $lat = $template['latitude'];
                    $lng = $template['longitude'];

                    $stmt = $pdo->prepare('INSERT INTO ads (user_id, title, pet_name, description, breed_id, district_id, status, photo_path, address, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                    try {
                        $stmt->execute([
                            $template['user_id'],
                            $title,
                            $petName,
                            $description,
                            $template['breed_id'],
                            $template['district_id'],
                            $status,
                            $template['photo_path'],
                            $address,
                            $lat,
                            $lng
                        ]);
                        $inserted++;
                    } catch (Throwable $e) {
                        continue;
                    }
                }

                $message = 'Бэкап сохранен в /exports. Сгенерировано объявлений: ' . $inserted . ' из ' . $count . '.';
            }
        }
    }
}

$pageTitle = 'Генератор данных';
require __DIR__ . '/includes/header.php';
?>
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h1 class="h4 mb-0">Генератор контента (Seeder)</h1>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-info"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_input() ?>
            <div class="mb-3">
                <label class="form-label">Выберите таблицу</label>
                <select name="table_name" class="form-select">
                    <?php foreach ($allowedTables as $t): ?>
                        <option value="<?= e($t) ?>"><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Рекомендуется выбирать ads. Для breeds и districts доступен только CSV-бэкап.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Сколько записей добавить?</label>
                <input type="number" name="count" class="form-control" value="10" min="1" max="100">
            </div>

            <button type="submit" class="btn btn-success w-100">Наполнить и сделать бэкап</button>
        </form>

        <a href="admin_panel.php" class="btn btn-secondary mt-3">← Вернуться в админку</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
