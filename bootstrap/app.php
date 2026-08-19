<?php

use App\Http\Middleware\CareEarthAuth;
use App\Http\Middleware\EnsureCanEdit;
use App\Http\Middleware\EnsureCareEarthAdmin;
use App\Http\Middleware\TrustEmployeePortalProxy;
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
        $middleware->trustProxies(at: '*');
        $middleware->prepend(TrustEmployeePortalProxy::class);
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\ValidateCsrfToken::class,
        );
        $middleware->validateCsrfTokens(except: [
            'internal/portal/*',
        ]);
        $middleware->alias([
            'careearth.auth' => CareEarthAuth::class,
            'careearth.admin' => EnsureCareEarthAdmin::class,
            'careearth.edit' => EnsureCanEdit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
