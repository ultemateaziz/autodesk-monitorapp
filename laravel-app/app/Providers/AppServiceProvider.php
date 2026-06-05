<?php

namespace App\Providers;

use App\Services\GeoIPService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeoIPService::class);
    }

    public function boot(): void
    {
        //
    }
}
