<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$error = '';
$success = '';

if (is_post()) {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $error = 'Введите название района.';
    } else {
        $check = $pdo->prepare('SELECT id FROM districts WHERE name = ? LIMIT 1');
        $check->execute([$name]);

        if ($check->fetch()) {
            $error = 'Такой район уже существует.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO districts (name) VALUES (?)');
            $stmt->execute([$name]);
            $success = 'Район успешно добавлен.';
        }
    }
}

$districts = $pdo->query('SELECT * FROM districts ORDER BY name ASC')->fetchAll();

$pageTitle = 'Управление районами';
require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Добавить район</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_input() ?>

                    <div class="mb-3">
                        <label class="form-label">Название района</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <button class="btn btn-primary">Сохранить район</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Список районов</h2>

                <?php if ($districts): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Название</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($districts as $district): ?>
                                    <tr>
                                        <td><?= (int)$district['id'] ?></td>
                                        <td><?= e($district['name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">Районов пока нет.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>