<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class StoreIpData extends Data
{
    public function __construct(
        public string $ip_address,
    )
    {
    }
}
