<?php
// templates/home.php
require __DIR__ . '/layout/header.php';
?>

<h2 class="mb-4">Объявления о пропавших животных</h2>

<div class="card mb-4 p-3 bg-light">
    <form action="index.php" method="GET" class="row g-3">
        <input type="hidden" name="page" value="home">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control"
                   placeholder="Поиск по виду, породе или описанию..."
                   value="<?= h($q) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="district" class="form-control"
                   placeholder="Район (например, Сормовский)"
                   value="<?= h($district) ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Найти</button>
        </div>
        <div class="col-12 text-end">
            <a href="index.php?page=home" class="text-muted text-decoration-none small">Сбросить фильтры</a>
        </div>
    </form>
</div>

<div class="row">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <?php
            $img = isset($post['image_url']) ? trim((string)$post['image_url']) : '';
            if ($img === '') $img = "https://via.placeholder.com/300x200?text=Нет+фото";
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="<?= h($img) ?>" class="card-img-top"
                         style="height:200px;object-fit:cover;" alt="Фото животного">
                    <div class="card-body">
                        <h5 class="card-title"><?= h($post['type_animal']) ?>, <?= h($post['breed_animal']) ?></h5>
                        <p class="mb-1"><strong>Окрас:</strong> <?= h($post['coloring_animal']) ?></p>
                        <p class="mb-1"><strong>Район:</strong> <?= h($post['district']) ?></p>
                        <p class="card-text mt-2"><?= h($post['description']) ?></p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <div class="d-flex flex-column gap-2">
                                <a href="index.php?page=edit_post&id=<?= (int)$post['id'] ?>" class="btn btn-warning btn-sm">✏️ Редактировать</a>
                                <form action="index.php?page=delete_post" method="POST" onsubmit="return confirm('Удалить объявление?');">
                                    <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">🗑️ Удалить</button>
                                </form>
                                <form action="index.php?page=make_order" method="POST">
                                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Я нашёл это животное</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form action="index.php?page=make_order" method="POST">
                                <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-primary w-100">Я нашёл это животное</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">Объявлений пока нет.</p>
    <?php endif; ?>
</div>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $query = array('page' => 'home', 'p' => $i);
                if ($q !== '') $query['q'] = $q;
                if ($district !== '') $query['district'] = $district;
                $url = 'index.php?' . http_build_query($query);
                ?>
                <li class="page-item <?= ($i === (int)$currentPage) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= h($url) ?>"><?= (int)$i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/layout/footer.php'; ?>

