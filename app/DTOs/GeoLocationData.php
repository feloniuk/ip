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

    public static function fromApiResponse(array $response, string $ipAddress): self
    {
        return new self(
            country: $response['country'] ?? null,
            city: $response['city'] ?? null,
            ip_address: $ipAddress
        );
    }
}