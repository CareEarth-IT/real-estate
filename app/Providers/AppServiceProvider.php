<?php

namespace App\Providers;

use App\Models\CareEarthUser;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $url = config('careearth.url', config('app.url'));

        if ($url) {
            URL::forceRootUrl($url);
        }

        Route::bind('user', fn (string $value) => CareEarthUser::query()->findOrFail($value));
    }
}
