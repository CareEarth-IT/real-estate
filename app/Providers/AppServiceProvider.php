<?php

namespace App\Providers;

use App\Http\Middleware\CareEarthAuth;
use App\Models\CareEarthUser;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->bound('request')) {
            $request = $this->app->make('request');

            if ($request->hasHeader('Host')) {
                $configuredPath = parse_url((string) config('app.url'), PHP_URL_PATH) ?: '';
                $root = rtrim($request->getSchemeAndHttpHost().rtrim($configuredPath, '/'), '/');

                if ($root !== '') {
                    URL::forceRootUrl($root);
                }
            }
        } elseif ($url = config('app.url')) {
            URL::forceRootUrl(rtrim((string) $url, '/'));
        }

        Route::bind('user', fn (string $value) => CareEarthUser::query()->findOrFail($value));

        View::composer('*', function ($view): void {
            $request = request();

            if (! $request->hasSession()) {
                return;
            }

            $view->with('canEdit', CareEarthAuth::canEdit($request));
            $view->with('canAccessPropertyMaster', CareEarthAuth::canAccessPropertyMaster($request));
            $view->with('canManageUsers', CareEarthAuth::canManageUsers($request));
        });
    }
}
