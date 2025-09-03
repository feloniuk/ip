<?php

namespace App\Providers;

use App\Api\GeoLocationApi;
use App\Contracts\GeoLocationApiInterface;
use App\Models\IpAddress;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Routing\ResponseFactory;
use Psr\Log\LoggerInterface;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // API интерфейс
        $this->app->bind(GeoLocationApiInterface::class, GeoLocationApi::class);

        $this->app->bind(IpAddress::class, function ($app) {
            return new IpAddress();
        });

        $this->app->bind(\App\Services\GeoLocationService::class, function ($app) {
            return new \App\Services\GeoLocationService(
                $app->make(GeoLocationApiInterface::class),
                $app->make(CacheRepository::class),
                $app->make(ConfigRepository::class)
            );
        });

        $this->app->bind(\App\Services\GeoLocationApiService::class, function ($app) {
            return new \App\Services\GeoLocationApiService(
                $app->make(HttpFactory::class),
                $app->make(ConfigRepository::class)
            );
        });

        $this->app->bind(\App\Services\IpService::class, function ($app) {
            return new \App\Services\IpService(
                $app->make(\App\Services\GeoLocationService::class),
                $app->make(IpAddress::class)
            );
        });

        $this->app->bind(\App\Services\AuthService::class, function ($app) {
            return new \App\Services\AuthService(
                $app->make(Guard::class),
                $app->make(LoggerInterface::class),
                $app->make(ValidationFactory::class)
            );
        });

        $this->app->bind(\App\Services\ExportService::class, function ($app) {
            return new \App\Services\ExportService(
                $app->make(ExcelWriter::class),
                $app->make(Carbon::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}