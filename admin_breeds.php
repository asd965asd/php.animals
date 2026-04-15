<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$error = '';
$success = '';

if (is_post()) {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $error = 'Введите название породы.';
    } else {
        $check = $pdo->prepare('SELECT id FROM breeds WHERE name = ? LIMIT 1');
        $check->execute([$name]);

        if ($check->fetch()) {
            $error = 'Такая порода уже существует.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO breeds (name) VALUES (?)');
            $stmt->execute([$name]);
            $success = 'Порода успешно добавлена.';
        }
    }
}

$breeds = $pdo->query('SELECT * FROM breeds ORDER BY name ASC')->fetchAll();

$pageTitle = 'Управление породами';
require __DIR__ . '/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Добавить породу</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_input() ?>

                    <div class="mb-3">
                        <label class="form-label">Название породы</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <button class="btn btn-primary">Сохранить породу</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Список пород</h2>

                <?php if ($breeds): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Название</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($breeds as $breed): ?>
                                    <tr>
                                        <td><?= (int)$breed['id'] ?></td>
                                        <td><?= e($breed['name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">Пород пока нет.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>