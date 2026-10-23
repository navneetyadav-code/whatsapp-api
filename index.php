<?php

declare(strict_types=1);

/**
 * WhatsGateway — Root Entry Point Redirector
 *
 * For web server configurations where DocumentRoot points to the repository root,
 * this file forwards incoming requests to the public/ front controller directory
 * while preserving any query parameters (e.g. ?page=docs or ?page=login).
 */

$queryString = $_SERVER['QUERY_STRING'] ?? '';
$target = 'public/' . ($queryString !== '' ? '?' . $queryString : '');

header('Location: ' . $target, true, 302);
exit;
