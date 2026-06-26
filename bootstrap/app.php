<?php

use App\Http\Middleware\CareEarthAuth;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureCareEarthAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'careearth.auth' => CareEarthAuth::class,
            'careearth.admin' => EnsureCareEarthAdmin::class,
            'admin.auth' => EnsureAdminAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
