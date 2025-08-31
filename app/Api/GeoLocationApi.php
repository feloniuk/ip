<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\GeoLocationApiInterface;
use App\Services\GeoLocationApiService;
use App\Http\Resources\IpAddressResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class GeoLocationApi implements GeoLocationApiInterface
{
    public function __construct(
        protected GeoLocationApiService $geoApiService,
    ) {}

    public function fetchGeoLocationData(string $ipAddress): AnonymousResourceCollection
    {
        $locationData = $this->geoApiService->fetchGeoLocationData($ipAddress);

        return IpAddressResource::collection([(object) $locationData]);
    }
}