<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if (!app_config('app.enable_test_page')) {
    http_response_code(404);
    exit('Not Found');
}

require dirname(__DIR__) . '/test/index.php';
