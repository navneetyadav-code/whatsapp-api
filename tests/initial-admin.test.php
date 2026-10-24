<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/lib/Auth.php';

function initial_admin_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class InitialAdminMemoryPdo extends PDO
{
    public array $users = [];
    public array $sessions = [];
    public array $webhooks = [];
    public bool $lockAvailable = true;
    private bool $transaction = false;
    private array $snapshot = [];

    public function __construct() {}

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new InitialAdminMemoryStatement($this, $query);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        if (str_contains($query, 'SELECT id, email FROM users')) {
            return new InitialAdminMemoryStatement($this, $query, array_slice($this->users, 0, 2));
        }
        throw new RuntimeException('Unexpected database query in test double.');
    }

    public function beginTransaction(): bool
    {
        $this->snapshot = [$this->users, $this->sessions, $this->webhooks];
        $this->transaction = true;
        return true;
    }

    public function commit(): bool
    {
        $this->transaction = false;
        return true;
    }

    public function rollBack(): bool
    {
        [$this->users, $this->sessions, $this->webhooks] = $this->snapshot;
        $this->transaction = false;
        return true;
    }

    public function inTransaction(): bool
    {
        return $this->transaction;
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return (string) count($this->users);
    }
}

final class InitialAdminMemoryStatement extends PDOStatement
{
    private mixed $scalar = null;

    public function __construct(private InitialAdminMemoryPdo $pdo, private string $query, private array $rows = []) {}

    public function execute(?array $params = null): bool
    {
        $params ??= [];
        if (str_contains($this->query, 'GET_LOCK')) {
            $this->scalar = $this->pdo->lockAvailable ? 1 : 0;
        } elseif (str_contains($this->query, 'RELEASE_LOCK')) {
            $this->scalar = 1;
        } elseif (str_starts_with($this->query, 'INSERT INTO users')) {
            $this->pdo->users[] = [
                'id' => count($this->pdo->users) + 1,
                'name' => $params[0],
                'email' => $params[1],
                'phone' => $params[2],
                'password_hash' => $params[3],
            ];
        } elseif (str_starts_with($this->query, 'INSERT INTO whatsapp_sessions')) {
            $this->pdo->sessions[] = ['user_id' => $params[0]];
        } elseif (str_starts_with($this->query, 'INSERT INTO webhooks')) {
            $this->pdo->webhooks[] = ['user_id' => $params[0]];
        } else {
            throw new RuntimeException('Unexpected prepared statement in test double.');
        }
        return true;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->scalar;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }
}

$password = 'a test-only long password';
$pdo = new InitialAdminMemoryPdo();
initial_admin_assert(
    create_initial_admin_account($pdo, 'Administrator', 'OWNER@example.test', $password) === 'created',
    'empty instance creates its first dashboard account'
);
initial_admin_assert(count($pdo->users) === 1 && count($pdo->sessions) === 1 && count($pdo->webhooks) === 1, 'bootstrap creates the user and existing related records transactionally');
initial_admin_assert(password_verify($password, $pdo->users[0]['password_hash']), 'bootstrap stores a password hash accepted by password_verify');
initial_admin_assert($pdo->users[0]['password_hash'] !== $password, 'bootstrap never stores the password in plaintext');
initial_admin_assert($pdo->users[0]['email'] === 'owner@example.test', 'bootstrap normalizes the email address');

$originalHash = $pdo->users[0]['password_hash'];
initial_admin_assert(
    create_initial_admin_account($pdo, 'Changed Name', 'owner@example.test', 'a different long password') === 'already_exists',
    'duplicate execution is an idempotent no-op'
);
initial_admin_assert(count($pdo->users) === 1 && $pdo->users[0]['password_hash'] === $originalHash, 'duplicate execution never replaces account data or password');

$ambiguous = new InitialAdminMemoryPdo();
$ambiguous->users[] = ['id' => 1, 'email' => 'existing@example.test', 'password_hash' => 'unchanged'];
try {
    create_initial_admin_account($ambiguous, 'Administrator', 'owner@example.test', $password);
    initial_admin_assert(false, 'existing unrelated users must refuse bootstrap');
} catch (RuntimeException $exception) {
    initial_admin_assert(count($ambiguous->users) === 1 && $ambiguous->users[0]['email'] === 'existing@example.test', 'refused bootstrap preserves existing users');
}

$invalid = new InitialAdminMemoryPdo();
foreach ([
    ['not-an-email', $password],
    ['owner@example.test', 'too-short'],
] as [$email, $candidatePassword]) {
    try {
        create_initial_admin_account($invalid, 'Administrator', $email, $candidatePassword);
        initial_admin_assert(false, 'invalid bootstrap configuration must be rejected');
    } catch (InvalidArgumentException) {
        initial_admin_assert($invalid->users === [], 'invalid configuration makes no database changes');
    }
}

$locked = new InitialAdminMemoryPdo();
$locked->lockAvailable = false;
try {
    create_initial_admin_account($locked, 'Administrator', 'owner@example.test', $password);
    initial_admin_assert(false, 'bootstrap must refuse when the advisory lock is unavailable');
} catch (RuntimeException) {
    initial_admin_assert($locked->users === [], 'lock failure makes no database changes');
}

$script = file_get_contents(dirname(__DIR__) . '/scripts/create-initial-admin.php') ?: '';
$frontController = file_get_contents(dirname(__DIR__) . '/public/index.php') ?: '';
$googleAuth = file_get_contents(dirname(__DIR__) . '/src/pages/google-auth.php') ?: '';
initial_admin_assert(str_contains($script, "PHP_SAPI !== 'cli'"), 'bootstrap command refuses non-CLI execution');
initial_admin_assert(!str_contains($frontController, 'create-initial-admin.php'), 'bootstrap command is not exposed as a public route');
initial_admin_assert(!is_dir(dirname(__DIR__) . '/public/scripts'), 'bootstrap command remains outside the document root');
initial_admin_assert(str_contains($googleAuth, "if (!app_config('app.enable_registration'))"), 'Google OAuth cannot create new accounts when public registration is disabled');

fwrite(STDOUT, "Initial admin bootstrap tests passed.\n");
