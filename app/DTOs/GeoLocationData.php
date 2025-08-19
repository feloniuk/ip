<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class GeoLocationData
{
    public function __construct(
        public ?string $country,
        public ?string $countryCode,
        public ?string $region,
        public ?string $regionName,
        public ?string $city,
        public ?string $zip,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $timezone,
        public ?string $isp,
        public ?string $org,
        public ?string $as,
        public array $rawResponse
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'country_code' => $this->countryCode,
            'region' => $this->region,
            'region_name' => $this->regionName,
            'city' => $this->city,
            'zip' => $this->zip,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'isp' => $this->isp,
            'org' => $this->org,
            'as' => $this->as,
            'raw_response' => $this->rawResponse,
            'geo_updated_at' => now(),
        ];
    }

    public function getFormattedLocation(): string
    {
        $parts = array_filter([
            $this->city,
            $this->regionName,
            $this->country
        ]);
        
        return implode(', ', $parts) ?: 'Unknown Location';
    }

    public function isComplete(): bool
    {
        return $this->country !== null && $this->city !== null;
    }

    public static function fromApiResponse(array $response): self
    {
        return new self(
            country: $response['country'] ?? null,
            countryCode: $response['countryCode'] ?? null,
            region: $response['region'] ?? null,
            regionName: $response['regionName'] ?? null,
            city: $response['city'] ?? null,
            zip: $response['zip'] ?? null,
            latitude: isset($response['lat']) ? (float) $response['lat'] : null,
            longitude: isset($response['lon']) ? (float) $response['lon'] : null,
            timezone: $response['timezone'] ?? null,
            isp: $response['isp'] ?? null,
            org: $response['org'] ?? null,
            as: $response['as'] ?? null,
            rawResponse: $response
        );
    }
}