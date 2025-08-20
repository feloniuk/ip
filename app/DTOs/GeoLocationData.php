<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO для передачі геолокаційних даних
 * Забезпечує типобезпеку та іммутабельність даних від геолокаційного API
 */
readonly class GeoLocationData
{
    /**
     * Конструктор для створення іммутабельного об'єкту з геоданими
     * 
     * @param string|null $country Назва країни
     * @param string|null $countryCode Код країни (ISO 3166-1 alpha-2)
     * @param string|null $region Код регіону
     * @param string|null $regionName Назва регіону
     * @param string|null $city Назва міста
     * @param string|null $zip Поштовий індекс
     * @param float|null $latitude Широта (-90 до 90)
     * @param float|null $longitude Довгота (-180 до 180)
     * @param string|null $timezone Часовий пояс
     * @param string|null $isp Інтернет провайдер
     * @param string|null $org Організація
     * @param string|null $as Автономна система
     * @param array<string, mixed> $rawResponse Повна відповідь від API
     */
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
     * Конвертує DTO в масив для збереження в базі даних
     * 
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

    /**
     * Повертає форматований рядок з місцезнаходженням
     */
    public function getFormattedLocation(): string
    {
        $parts = array_filter([
            $this->city,
            $this->regionName,
            $this->country
        ]);
        
        return !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
    }

    /**
     * Перевіряє, чи є геолокаційні дані повними
     * Мінімально необхідні: країна та місто
     */
    public function isComplete(): bool
    {
        return !empty($this->country) && !empty($this->city);
    }

    /**
     * Перевіряє, чи доступні координати
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Валідує координати на правильність
     */
    public function hasValidCoordinates(): bool
    {
        if (!$this->hasCoordinates()) {
            return false;
        }

        return $this->latitude >= -90 && $this->latitude <= 90 &&
               $this->longitude >= -180 && $this->longitude <= 180;
    }

    /**
     * Створює екземпляр з сирих даних API
     * 
     * @param array<string, mixed> $apiResponse
     */
    public static function fromApiResponse(array $apiResponse): self
    {
        return new self(
            country: $apiResponse['country'] ?? null,
            countryCode: $apiResponse['countryCode'] ?? null,
            region: $apiResponse['region'] ?? null,
            regionName: $apiResponse['regionName'] ?? null,
            city: $apiResponse['city'] ?? null,
            zip: $apiResponse['zip'] ?? null,
            latitude: isset($apiResponse['lat']) ? (float) $apiResponse['lat'] : null,
            longitude: isset($apiResponse['lon']) ? (float) $apiResponse['lon'] : null,
            timezone: $apiResponse['timezone'] ?? null,
            isp: $apiResponse['isp'] ?? null,
            org: $apiResponse['org'] ?? null,
            as: $apiResponse['as'] ?? null,
            rawResponse: $apiResponse
        );
    }
}