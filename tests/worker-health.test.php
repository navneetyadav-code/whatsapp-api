<?php

declare(strict_types=1);

function health_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

putenv('APP_ENV=local');
putenv('WORKER_URL=');
putenv('WORKER_PORT=3210');
$config = require dirname(__DIR__) . '/config/app.php';

function app_config(?string $key = null): mixed
{
    global $config;
    return $key === null ? $config : ($config[$key] ?? null);
}

require_once dirname(__DIR__) . '/src/lib/WorkerClient.php';

health_assert(
    $config['worker']['url'] === 'http://127.0.0.1:3210',
    'missing WORKER_URL uses the loopback URL derived from WORKER_PORT'
);
health_assert(
    worker_request_url('http://127.0.0.1:3210/', '/health') === 'http://127.0.0.1:3210/health',
    'health request targets the registered root /health route'
);
health_assert(
    worker_safe_target('https://user:pass@example.invalid/wrong/path?token=hidden#fragment') === 'https://example.invalid/wrong/path',
    'diagnostic target omits credentials, query strings, and fragments'
);
health_assert(
    worker_safe_response_excerpt('{"error":"Invalid worker token","access_token":"hidden"}') === '{"error":"Invalid worker token","access_token":"[redacted]"}',
    'diagnostic response excerpt redacts secret fields'
);
health_assert(worker_loopback_proxy_override('http://127.0.0.1:3210/health') === '', 'loopback worker bypasses inherited proxies');
health_assert(worker_loopback_proxy_override('https://worker.example/health') === null, 'non-loopback worker does not change proxy settings');
health_assert(worker_safe_content_type('application/json; charset=utf-8') === 'application/json', 'content type logging strips parameters');

$healthHeaders = worker_request_headers('/health', "ignored\r\nheader: value");
health_assert(is_array($healthHeaders), 'health request headers are available even if an unused token contains controls');
health_assert(!in_array('X-Worker-Token: ignored', $healthHeaders, true), 'health request omits the worker token header');
health_assert(count($healthHeaders) === 1 && $healthHeaders[0] === 'Content-Type: application/json', 'health request keeps only the fixed content type header');

$protectedHeaders = worker_request_headers('queue/notify', 'valid-token');
health_assert(is_array($protectedHeaders), 'protected request accepts a token without control characters');
health_assert(in_array('X-Worker-Token: valid-token', $protectedHeaders, true), 'protected request retains its worker token header');
foreach (["bad\rvalue", "bad\nvalue", "bad\0value", "bad\x7Fvalue"] as $unsafeToken) {
    health_assert(worker_request_headers('queue/notify', $unsafeToken) === null, 'protected request rejects ASCII control characters in the token');
}

$observed400 = worker_decode_response('', 400);
health_assert(($observed400['status'] ?? null) === 400 && !empty($observed400['invalid_json']), 'reproduces empty HTTP 400 invalid JSON response');
health_assert(!worker_health_ready($observed400), 'empty HTTP 400 response cannot pass readiness');

$ready = [
    'ok' => true,
    'status_code' => 200,
    'service' => 'whatsapp-worker',
    'database' => ['connected' => true, 'schema_ready' => true],
];
health_assert(worker_health_ready($ready), 'healthy worker and ready database pass');

$databaseNotReady = $ready;
$databaseNotReady['status_code'] = 503;
$databaseNotReady['ok'] = false;
$databaseNotReady['database']['schema_ready'] = false;
health_assert(!worker_health_ready($databaseNotReady), 'database-not-ready response fails readiness');

$invalidToken = $ready;
$invalidToken['status_code'] = 401;
$invalidToken['ok'] = false;
health_assert(!worker_health_ready($invalidToken), 'unauthorized worker response fails readiness');

$badRequest = $ready;
$badRequest['status_code'] = 400;
health_assert(!worker_health_ready($badRequest), 'unexpected HTTP 400 response fails readiness');
$wrongHandler = ['ok' => false, 'status_code' => 400, 'error' => 'Bad Request'];
health_assert(!worker_health_ready($wrongHandler), 'HTTP 400 from a handler without worker health fields is rejected');

$unreachable = ['ok' => false, 'status_code' => 0];
health_assert(!worker_health_ready($unreachable), 'unreachable worker fails readiness');

fwrite(STDOUT, "Worker health contract tests passed.\n");
