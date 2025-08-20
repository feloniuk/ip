<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\IpAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API ресурс для форматування відповідей з IP адресами
 * Структурує дані та приховує чутливу інформацію залежно від ролі користувача
 * 
 * @mixin IpAddress
 */
class IpAddressResource extends JsonResource
{
    /**
     * Трансформує модель в масив для API відповіді
     * 
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var IpAddress $this */
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            
            // Групуємо локаційну інформацію
            'location' => [
                'country' => $this->country,
                'country_code' => $this->country_code,
                'region' => $this->region,
                'region_name' => $this->region_name,
                'city' => $this->city,
                'zip' => $this->zip,
                'formatted_location' => $this->location, // Використовуємо акцесор
            ],
            
            // Координати
            'coordinates' => $this->when($this->hasCoordinates(), [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]),
            
            // Мережева інформація
            'network_info' => [
                'timezone' => $this->timezone,
                'isp' => $this->isp,
                'org' => $this->org,
                'as' => $this->as,
            ],
            
            // Метадані
            'metadata' => [
                'geo_updated_at' => $this->geo_updated_at?->toISOString(),
                'is_geo_data_outdated' => $this->isGeoDataOutdated(),
                'created_by' => $this->whenLoaded('creator', function (): array {
                    return [
                        'id' => $this->creator->id,
                        'name' => $this->creator->name,
                        'email' => $this->creator->email,
                    ];
                }),
                'created_at' => $this->created_at->toISOString(),
                'updated_at' => $this->updated_at->toISOString(),
            ],
            
            // Показуємо сирі дані API тільки адмінам
            $this->mergeWhen(
                $request->user()?->hasRole('admin') ?? false,
                ['raw_api_response' => $this->raw_response]
            ),
        ];
    }

    /**
     * Додаткові дані для відповіді
     * 
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}