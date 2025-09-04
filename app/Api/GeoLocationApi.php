<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\GeoLocationApiInterface;
use App\Services\GeoLocationApiService;
use App\DTOs\GeoLocationData;

final readonly class GeoLocationApi implements GeoLocationApiInterface
{
    public function __construct(
        private GeoLocationApiService $geoApiService,
    ) {}

    public function fetchGeoLocationData(string $ipAddress): GeoLocationData
    {
        $apiResponse = $this->geoApiService->fetchGeoLocationData($ipAddress);
        
        return new GeoLocationData(
            country: $apiResponse['country'] ?? null,
            city: $apiResponse['city'] ?? null,
            ip_address: $ipAddress
        );
    }
}