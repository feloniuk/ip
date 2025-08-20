<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Модель користувача з підтримкою ролей та дозволів
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Carbon\Carbon $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Guard для Spatie Permission
     */
    protected $guard_name = 'sanctum';

    /**
     * Поля, які можна масово заповнювати
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Приховані поля при серіалізації
     * 
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Типізація атрибутів
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Зв'язок з IP адресами, які створив користувач
     */
    public function createdIpAddresses(): HasMany
    {
        return $this->hasMany(IpAddress::class, 'created_by');
    }

    /**
     * Перевіряє, чи є користувач адміністратором
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Перевіряє, чи є користувач звичайним користувачем
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }
}