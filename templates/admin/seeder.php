<?php
require __DIR__ . '/../layout/header.php';
?>

<h2 class="mb-3">Seeder (генератор объявлений)</h2>
<a href="index.php?page=admin" class="btn btn-secondary mb-3">← В админку</a>

<?= $seedMessage ?>

<form method="POST" action="index.php?page=admin_seeder" class="card p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
    <div class="mb-3">
        <label class="form-label">Сколько записей сгенерировать</label>
        <input type="number" name="count" class="form-control" min="1" max="500" value="20">
    </div>
    <div class="alert alert-warning">
        Сидер берёт случайное существующее объявление как шаблон, делает backup в <code>exports/</code> и добавляет копии.
    </div>
    <button type="submit" class="btn btn-info">Запустить Seeder</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>

