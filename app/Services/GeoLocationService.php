<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GeoLocationApiInterface;
use App\DTOs\GeoLocationData;
use App\Exceptions\GeoLocationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

final class GeoLocationService
{
    public function __construct(
        private GeoLocationApiInterface $geoLocationApi
    ) {}

    public function getGeoLocation(string $ipAddress): GeoLocationData
    {
        $this->validateIpAddress($ipAddress);

        $apiData = $this->geoLocationApi->fetchGeoLocationData($ipAddress);

        $dataArray = $apiData->toArray(request())['data'] ?? [];
        $firstItem = $dataArray[0] ?? [];

        return new GeoLocationData($firstItem['country'] ?? null,
        $firstItem['city'] ?? null,
        $ipAddress);
    }

    private function validateIpAddress(string $ipAddress): void
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }
    }
}