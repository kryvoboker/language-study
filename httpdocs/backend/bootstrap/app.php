<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Project-specific exception mappings can be registered here.
    })->create();

$new_storage_path = getenv('NEW_STORAGE_PATH');

if (empty($new_storage_path)) {
    exit('NEW_STORAGE_PATH environment variable must be set!');
}

// Override storage path immediately after app creation
$app->useStoragePath($new_storage_path);

return $app;
