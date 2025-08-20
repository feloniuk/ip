<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Модель для зберігання IP адрес та їх геолокаційної інформації
 * 
 * @property int $id
 * @property string $ip_address
 * @property string|null $country
 * @property string|null $country_code
 * @property string|null $region
 * @property string|null $region_name
 * @property string|null $city
 * @property string|null $zip
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $timezone
 * @property string|null $isp
 * @property string|null $org
 * @property string|null $as
 * @property array|null $raw_response
 * @property Carbon|null $geo_updated_at
 * @property int|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class IpAddress extends Model
{
    use HasFactory;

    /**
     * Назва таблиці в базі даних
     */
    protected $table = 'ip_addresses';

    /**
     * Поля, які можна масово заповнювати
     * 
     * @var array<string>
     */
    protected $fillable = [
        'ip_address',
        'country',
        'country_code',
        'region',
        'region_name',
        'city',
        'zip',
        'latitude',
        'longitude',
        'timezone',
        'isp',
        'org',
        'as',
        'raw_response',
        'geo_updated_at',
        'created_by',
    ];

    /**
     * Типізація атрибутів моделі
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'raw_response' => 'array',
        'geo_updated_at' => 'datetime',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Приховані атрибути при серіалізації
     * 
     * @var array<string>
     */
    protected $hidden = [
        'raw_response', // Приховуємо сирі дані API за замовчуванням
    ];

    /**
     * Зв'язок з користувачем, який створив запис
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Скоуп для фільтрації по країні
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $country
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Скоуп для фільтрації по місту
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $city
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Скоуп для отримання IP з застарілими геоданими
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $daysOld За замовчуванням 30 днів
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithOutdatedGeoData($query, int $daysOld = 30)
    {
        return $query->where(function ($q) use ($daysOld): void {
            $q->whereNull('geo_updated_at')
              ->orWhere('geo_updated_at', '<', now()->subDays($daysOld));
        });
    }

    /**
     * Акцесор для отримання форматованого місцезнаходження
     */
    public function getLocationAttribute(): string
    {
        $parts = array_filter([
            $this->city,
            $this->region_name,
            $this->country
        ]);
        
        return !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
    }

    /**
     * Перевіряє, чи є геодані застарілими
     */
    public function isGeoDataOutdated(int $daysThreshold = 30): bool
    {
        if ($this->geo_updated_at === null) {
            return true;
        }

        return $this->geo_updated_at->lt(now()->subDays($daysThreshold));
    }

    /**
     * Перевіряє, чи є координати доступними
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Повертає координати у вигляді масиву
     * 
     * @return array{latitude: float|null, longitude: float|null}
     */
    public function getCoordinates(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}