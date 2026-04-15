<?php

class User
{
    /**
     * @var PDO
     */
    private $db;

    public function __construct()
    {
        // Единый способ подключения: через db.php
        if (!isset($GLOBALS['pdo']) || !($GLOBALS['pdo'] instanceof PDO)) {
            require __DIR__ . '/../../db.php';
        }
        $this->db = $GLOBALS['pdo'];
    }

    // Регистрация пользователя (Create)
    public function create($email, $passwordPlain)
    {
        $sql = "INSERT INTO users (email, password_hash, role) 
                VALUES (:email, :hash, 'client')";

        $stmt = $this->db->prepare($sql);

        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

        return $stmt->execute(array(
            ':email' => $email,
            ':hash'  => $hash,
        ));
    }

    // Поиск по email (Read)
    public function findByEmail($email)
    {
        // Не тянем необязательные колонки (например avatar_url), чтобы регистрация не ломалась,
        // если миграции ещё не применены.
        $stmt = $this->db->prepare("SELECT id, email, password_hash, role, created_at FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(array(':email' => $email));
        $row = $stmt->fetch();

        return $row ? $row : null;
    }
}

