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
        $isSsl = !empty(self::$config['ssl']) || str_contains((string) (self::$config['dsn'] ?? ''), 'tidbcloud.com');
        if ($isSsl) {
            $configuredCa = (string) (self::$config['ssl_ca'] ?? '');
            $caCandidates = array_filter([$configuredCa,
                '/etc/ssl/certs/ca-certificates.crt',
                dirname(__DIR__, 2) . '/prod/isrgrootx1.pem',
                dirname(__DIR__, 2) . '/produ/isrgrootx1.pem',
            ]);
            $caFound = null;
            foreach ($caCandidates as $ca) {
                if (is_file($ca) && is_readable($ca)) {
                    $caFound = $ca;
                    break;
                }
            }

            if ($caFound) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $caFound;
            }
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
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
