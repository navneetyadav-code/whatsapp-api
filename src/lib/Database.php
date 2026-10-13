<?php

declare(strict_types=1);

final class Database
{
    private static array $config;
    private static ?PDO $pdo = null;

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        if (!empty(self::$config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        self::$pdo = new PDO(
            self::$config['dsn'],
            self::$config['user'],
            self::$config['password'],
            $options
        );

        return self::$pdo;
    }
}
