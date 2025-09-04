<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $ip_address
 * @property string|null $country
 * @property string|null $city
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class IpAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'country',
        'city',
    ];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getFormattedLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->country]);
        return !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
    }

    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', 'LIKE', "%{$country}%");
    }

    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'LIKE', "%{$city}%");
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('ip_address', 'LIKE', "%{$search}%")
              ->orWhere('country', 'LIKE', "%{$search}%")
              ->orWhere('city', 'LIKE', "%{$search}%");
        });
    }
}