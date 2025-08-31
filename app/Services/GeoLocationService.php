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

        $cacheKey = Config::get('geolocation.cache.prefix', 'geo:ip:') . $ipAddress;
        $cacheTtl = Config::get('geolocation.cache.ttl', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($ipAddress) {
            $apiData = $this->geoLocationApi->fetchGeoLocationData($ipAddress);

            // Преобразуем ResourceCollection в массив
            $dataArray = $apiData->toArray(request())['data'] ?? [];
            $firstItem = $dataArray[0] ?? [];

            return GeoLocationData::fromApiResponse($firstItem, $ipAddress);
        });
    }

    private function validateIpAddress(string $ipAddress): void
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }
    }
}