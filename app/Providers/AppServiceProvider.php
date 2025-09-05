<?php

namespace App\Providers;

use App\Api\GeoLocationApi;
use App\Contracts\GeoLocationApiInterface;
use App\Models\IpAddress;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Psr\Log\LoggerInterface;
use Maatwebsite\Excel\Excel as ExcelWriter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GeoLocationApiInterface::class, function ($app) {
            return new GeoLocationApi(
                $app->make(HttpFactory::class),
                $app->make(ConfigRepository::class)
            );
        });

        $this->app->bind(\App\Services\GeoLocationService::class, function ($app) {
            return new \App\Services\GeoLocationService(
                $app->make(GeoLocationApiInterface::class)
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
                $app->make(LoggerInterface::class),
                $app->make(ValidationFactory::class)
            );
        });

        $this->app->bind(\App\Services\ExportService::class, function ($app) {
            return new \App\Services\ExportService(
                $app->make(ExcelWriter::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}