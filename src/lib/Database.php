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

        try {
            self::$pdo = new PDO(
                self::$config['dsn'],
                self::$config['user'],
                self::$config['password'],
                $options
            );
            return self::$pdo;
        } catch (PDOException $e) {
            $code = (int) ($e->errorInfo[1] ?? 0);
            if ($code !== 1049) {
                throw $e;
            }

            // Database was unknown (error 1049).
            // 1. Direct fallback: connect to 'test' (standard TiDB Cloud default schema)
            try {
                $testDsn = preg_replace('/dbname=[^;]+/i', 'dbname=test', (string) self::$config['dsn']);
                $testPdo = new PDO($testDsn, self::$config['user'], self::$config['password'], $options);
                self::$pdo = $testPdo;
                self::$config['dsn'] = $testDsn;
                putenv('DB_NAME=test');
                $_ENV['DB_NAME'] = 'test';
                return self::$pdo;
            } catch (Throwable) {
                // Not using 'test' fallback, continue to dynamic discovery
            }

            $requestedDb = 'whatsapp_api';
            if (preg_match('/dbname=([^;]+)/i', (string) (self::$config['dsn'] ?? ''), $matches)) {
                $requestedDb = trim($matches[1]);
            }

            // Candidate connection DSNs to reach the server:
            // 1. Connect pointing to 'test' (default schema on TiDB Cloud Serverless)
            // 2. Connect with dbname stripped entirely
            $candidates = [
                preg_replace('/dbname=[^;]+/i', 'dbname=test', (string) self::$config['dsn']),
                preg_replace('/dbname=[^;]+;?/i', '', (string) self::$config['dsn']),
            ];

            $probePdo = null;
            $probeConnectedDb = null;
            foreach ($candidates as $candidateDsn) {
                try {
                    $probePdo = new PDO(
                        $candidateDsn,
                        self::$config['user'],
                        self::$config['password'],
                        $options
                    );
                    $probeConnectedDb = (string) $probePdo->query('SELECT DATABASE()')->fetchColumn();
                    break;
                } catch (Throwable) {
                    continue;
                }
            }

            if (!$probePdo instanceof PDO) {
                throw $e;
            }

            // 1. Attempt to create the requested database
            try {
                $probePdo->exec("CREATE DATABASE IF NOT EXISTS `{$requestedDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$pdo = new PDO(
                    self::$config['dsn'],
                    self::$config['user'],
                    self::$config['password'],
                    $options
                );
                return self::$pdo;
            } catch (Throwable) {
                // CREATE DATABASE denied or failed on serverless cluster
            }

            // 2. Discover accessible databases via SHOW DATABASES
            try {
                $databases = $probePdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $systemDbs = ['information_schema', 'performance_schema', 'mysql', 'sys', 'metrics_schema'];
                $userDbs = array_values(array_filter($databases, function ($db) use ($systemDbs) {
                    return !in_array(strtolower((string) $db), $systemDbs, true);
                }));

                // Fallback to 'test' if present (standard TiDB Cloud default schema)
                if (in_array('test', $userDbs, true)) {
                    $targetDsn = preg_replace('/dbname=[^;]+/i', 'dbname=test', (string) self::$config['dsn']);
                    self::$pdo = new PDO($targetDsn, self::$config['user'], self::$config['password'], $options);
                    self::$config['dsn'] = $targetDsn;
                    putenv('DB_NAME=test');
                    $_ENV['DB_NAME'] = 'test';
                    return self::$pdo;
                }

                // Fallback to first available user database
                if (!empty($userDbs[0])) {
                    $firstDb = (string) $userDbs[0];
                    $targetDsn = preg_replace('/dbname=[^;]+/i', 'dbname=' . $firstDb, (string) self::$config['dsn']);
                    self::$pdo = new PDO($targetDsn, self::$config['user'], self::$config['password'], $options);
                    self::$config['dsn'] = $targetDsn;
                    putenv("DB_NAME={$firstDb}");
                    $_ENV['DB_NAME'] = $firstDb;
                    return self::$pdo;
                }
            } catch (Throwable) {
                // Schema inspection failed
            }

            if ($probeConnectedDb !== '' && $probeConnectedDb !== null) {
                self::$pdo = $probePdo;
                putenv("DB_NAME={$probeConnectedDb}");
                $_ENV['DB_NAME'] = $probeConnectedDb;
                return self::$pdo;
            }

            throw $e;
        }
    }
}
