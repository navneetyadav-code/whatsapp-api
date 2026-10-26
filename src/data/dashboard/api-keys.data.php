<?php
declare(strict_types=1);

$apiKeys = list_api_keys((int) $user['id']);
$activeApiKeyCount = count(array_filter($apiKeys, static fn (array $key): bool => ($key['status'] ?? '') === 'active'));

$newCredentials = $_SESSION['new_api_credentials'] ?? null;
unset($_SESSION['new_api_credentials']);
