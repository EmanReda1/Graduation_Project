<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

      ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // ... أي aliases موجودة مسبقاً هنا (إذا وجدت) ...
            'jwt.auth' => CheckForToken::class, // قم بتغيير هذا السطر
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        // استثناءات ممكن تضيفها هنا لو حبيت
    })
    ->create();
