<?php

declare(strict_types=1);

namespace App\Contracts;

interface GeoLocationApiInterface
{
    /**
     * @param string $ipAddress
     * @return array API Data
     * @throws \App\Exceptions\GeoLocationException
     */
    public function fetchGeoLocationData(string $ipAddress): array;
}