<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Http\Resources\Json\ResourceCollection;

interface GeoLocationApiInterface
{
    /**
     * @param string $ipAddress
     * @return ResourceCollection API Data
     * @throws \App\Exceptions\GeoLocationException
     */
    public function fetchGeoLocationData(string $ipAddress): ResourceCollection;
}