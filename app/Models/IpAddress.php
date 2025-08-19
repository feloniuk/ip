<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
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
 * @property-read User|null $creator
 */
class IpAddress extends Model
{
    use HasFactory;

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

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'raw_response' => 'array',
        'geo_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_by' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    public function getLocationAttribute(): string
    {
        $parts = array_filter([
            $this->city,
            $this->region_name,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }

    public function isGeoDataOutdated(): bool
    {
        if ($this->geo_updated_at === null) {
            return true;
        }

        return $this->geo_updated_at->lt(now()->subDays(30));
    }
}