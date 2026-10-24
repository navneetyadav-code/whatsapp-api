<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/src/bootstrap.php';

$email = getenv('INITIAL_ADMIN_EMAIL');
$password = getenv('INITIAL_ADMIN_PASSWORD');
$name = getenv('INITIAL_ADMIN_NAME');

if (!is_string($email) || !is_string($password) || trim($email) === '' || $password === '') {
    fwrite(STDERR, "Set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD in the deployment environment before running this command.\n");
    exit(2);
}

try {
    $result = create_initial_admin_account(
        Database::pdo(),
        is_string($name) && trim($name) !== '' ? $name : 'Administrator',
        $email,
        $password
    );

    fwrite(STDOUT, $result === 'created'
        ? "Initial dashboard account created. You may now sign in.\n"
        : "The requested initial account already exists; no changes were made.\n");
} catch (InvalidArgumentException $exception) {
    fwrite(STDERR, '[initial-admin] ' . $exception->getMessage() . "\n");
    exit(2);
} catch (Throwable $exception) {
    fwrite(STDERR, '[initial-admin] Setup failed (' . get_class($exception) . '). No credentials were logged.\n');
    exit(1);
}
