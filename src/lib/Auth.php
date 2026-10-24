<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        redirect_to('index.php?page=login');
    }

    return $user;
}

function attempt_login(string $email, string $password): bool
{
    $email = mb_strtolower(trim($email));
    if (login_lockout_seconds($email) > 0) {
        return false;
    }

    $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        record_failed_login($email);
        return false;
    }

    clear_failed_logins($email);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return true;
}

function login_lockout_seconds(string $email): int
{
    $maxAttempts = max(1, (int) app_config('security.login_max_attempts'));
    $lockoutMinutes = max(1, (int) app_config('security.login_lockout_minutes'));

    try {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = ? AND ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([mb_strtolower(trim($email)), client_ip(), $lockoutMinutes]);
        $attempts = (int) $stmt->fetchColumn();
    } catch (Throwable) {
        return 0;
    }

    return $attempts >= $maxAttempts ? $lockoutMinutes * 60 : 0;
}

function record_failed_login(string $email): void
{
    try {
        $stmt = Database::pdo()->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)');
        $stmt->execute([mb_strtolower(trim($email)), client_ip()]);
    } catch (Throwable) {
        // Login throttling is defense-in-depth; do not break authentication if migration is pending.
    }
}

function clear_failed_logins(string $email): void
{
    try {
        $stmt = Database::pdo()->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?');
        $stmt->execute([mb_strtolower(trim($email)), client_ip()]);
    } catch (Throwable) {
        // See record_failed_login().
    }
}

function register_user(string $name, string $email, string $phone, string $password): array
{
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    try {
        $userId = create_user_records($pdo, $name, $email, $phone, $password);
        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;

        return [];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function create_user_records(PDO $pdo, string $name, string $email, string $phone, string $password): int
{
    $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        trim($name),
        function_exists('mb_strtolower') ? mb_strtolower(trim($email)) : strtolower(trim($email)),
        trim($phone),
        password_hash($password, PASSWORD_DEFAULT),
    ]);

    $userId = (int) $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO whatsapp_sessions (user_id) VALUES (?)')->execute([$userId]);
    $pdo->prepare('INSERT INTO webhooks (user_id) VALUES (?)')->execute([$userId]);
    return $userId;
}

function create_initial_admin_account(PDO $pdo, string $name, string $email, string $password): string
{
    $name = trim($name);
    $email = function_exists('mb_strtolower') ? mb_strtolower(trim($email)) : strtolower(trim($email));

    if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        throw new InvalidArgumentException('Provide a valid administrator name and email address.');
    }
    if (strlen($password) < 12) {
        throw new InvalidArgumentException('The initial administrator password must contain at least 12 characters.');
    }

    // TiDB supports MySQL-compatible advisory locks. This serializes concurrent
    // bootstrap invocations without changing the application schema.
    $lock = $pdo->prepare('SELECT GET_LOCK(?, ?)');
    $lock->execute(['whatsapp_api_initial_admin_setup', 10]);
    if ((int) $lock->fetchColumn() !== 1) {
        throw new RuntimeException('Could not acquire the initial account setup lock. Retry later.');
    }

    try {
        $pdo->beginTransaction();
        $rows = $pdo->query('SELECT id, email FROM users ORDER BY id ASC LIMIT 2 FOR UPDATE')->fetchAll(PDO::FETCH_ASSOC);
        if ($rows !== []) {
            $pdo->rollBack();
            if (count($rows) === 1 && strtolower((string) $rows[0]['email']) === $email) {
                return 'already_exists';
            }
            throw new RuntimeException('Initial account setup refused because the instance already contains user accounts.');
        }

        create_user_records($pdo, $name, $email, '', $password);
        $pdo->commit();
        return 'created';
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    } finally {
        $release = $pdo->prepare('SELECT RELEASE_LOCK(?)');
        $release->execute(['whatsapp_api_initial_admin_setup']);
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}
