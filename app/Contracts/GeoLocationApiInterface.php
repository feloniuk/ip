<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\GeoLocationData;

interface GeoLocationApiInterface
{
    /**
     * @param string $ipAddress
     * @return GeoLocationData
     * @throws \App\Exceptions\GeoLocationException
     */
    public function fetchGeoLocationData(string $ipAddress): GeoLocationData;
}