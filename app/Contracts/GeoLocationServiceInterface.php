<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\GeoLocationData;

interface GeoLocationServiceInterface
{
    public function getGeoLocation(string $ipAddress): GeoLocationData;
}