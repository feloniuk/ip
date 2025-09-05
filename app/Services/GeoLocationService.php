<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GeoLocationApiInterface;
use App\DTOs\GeoLocationData;

final class GeoLocationService
{
    public function __construct(
        private GeoLocationApiInterface $geoLocationApi
    ) {}

    public function getGeoLocation(string $ipAddress): GeoLocationData
    {
        return $this->geoLocationApi->fetchGeoLocationData($ipAddress);
    }
}