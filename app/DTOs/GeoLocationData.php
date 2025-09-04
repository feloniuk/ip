<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class GeoLocationData
{
    public function __construct(
        public ?string $country,
        public ?string $city,
        public string $ip_address,
    ) {}

    public function toArray(): array
    {
        return [
            'ip_address' => $this->ip_address,
            'country' => $this->country,
            'city' => $this->city,
        ];
    }

    public function getFormattedLocation(): string
    {
        $parts = array_filter([$this->city, $this->country]);
        return !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
    }
}