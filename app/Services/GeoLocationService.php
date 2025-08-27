<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GeoLocationApiInterface;
use App\Contracts\GeoLocationServiceInterface;
use App\DTOs\GeoLocationData;
use App\Exceptions\GeoLocationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

final readonly class GeoLocationService implements GeoLocationServiceInterface
{
    public function __construct(
        private GeoLocationApiInterface $geoLocationApi
    ) {}

    public function getGeoLocation(string $ipAddress): GeoLocationData
    {
        $this->validateIpAddress($ipAddress);

        $cacheKey = Config::get('geolocation.cache.prefix') . $ipAddress;
        $cacheTtl = Config::get('geolocation.cache.ttl');
        
        return Cache::remember($cacheKey, $cacheTtl, function () use ($ipAddress) {
            $apiData = $this->geoLocationApi->fetchGeoLocationData($ipAddress);
            
            return GeoLocationData::fromApiResponse($apiData, $ipAddress);
        });
    }

    private function validateIpAddress(string $ipAddress): void
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }
    }
}