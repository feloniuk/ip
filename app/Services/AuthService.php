<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GeoLocationApiInterface;
use App\DTOs\GeoLocationData;
use App\Exceptions\GeoLocationException;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Config;

final class AuthService extends ServiceProvider
{
    public function __construct(
        private GeoLocationApiInterface $geoLocationApi
    ) {}

    public function fetchGeoLocationData(string $ipAddress): GeoLocationData
    {
        $this->validateIpAddress($ipAddress);

        $apiData = $this->geoLocationApi->fetchGeoLocationData($ipAddress);

        return GeoLocationData::fromApiResponse($apiData, $ipAddress);
    }

    private function validateIpAddress(string $ipAddress): void
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }
    }
}