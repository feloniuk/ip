<?php

declare(strict_types=1);

namespace App\Filters;

use EloquentFilter\ModelFilter;

class IpAddressFilter extends ModelFilter
{
    public function country(string $country): self
    {
        return $this->where('country', 'LIKE', "%{$country}%");
    }

    public function city(string $city): self
    {
        return $this->where('city', 'LIKE', "%{$city}%");
    }

    public function ipAddress(string $ip): self
    {
        return $this->where('ip_address', 'LIKE', "%{$ip}%");
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
        });
    }
}