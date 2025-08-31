<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}