<?php
// db.php — обратная совместимость (старые файлы делают require 'db.php')
require_once __DIR__ . '/config/bootstrap.php';

// После bootstrap доступны:
// - $pdo (PDO)
// - h(), log_exception(), render()
// - $_SESSION['csrf_token']