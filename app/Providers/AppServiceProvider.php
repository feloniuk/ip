<?php

namespace App\Providers;

use App\Api\GeoLocationApi;
use App\Contracts\GeoLocationApiInterface;
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
        $this->app->bind(GeoLocationApiInterface::class, GeoLocationApi::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
