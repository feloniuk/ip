<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class StoreIpData extends Data
{
    public function __construct(
        public string $ip_address
    ) {}
}