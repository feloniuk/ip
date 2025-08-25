<?php

namespace App\Providers;

use App\Contracts\GeoLocationServiceInterface;
use App\Contracts\IpServiceInterface;
use App\Services\GeoLocationService;
use App\Services\IpService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GeoLocationServiceInterface::class, GeoLocationService::class);
        $this->app->bind(IpServiceInterface::class, IpService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
