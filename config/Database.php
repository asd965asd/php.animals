<?php
// Обертка над PDO (Singleton) для курсовой архитектуры

class Database
{
    /**
     * @var PDO|null
     */
    private static $instance = null;

    // Закрываем конструктор, чтобы нельзя было сделать new Database()
    private function __construct()
    {
    }

    /**
     * Возвращает единый экземпляр PDO
     *
     * @return PDO
     */
    public static function getConnection()
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';

            $dsn = 'mysql:host=' . $config['host'] .
                ';dbname=' . $config['db'] .
                ';charset=' . $config['charset'];

            $options = array(
                // Режим исключений
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Ассоциативный массив по умолчанию
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Отключаем эмуляцию prepare (лучше против SQLi)
                PDO::ATTR_EMULATE_PREPARES   => false,
            );

            $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);

            self::$instance = $pdo;
        }

        return self::$instance;
    }
}

