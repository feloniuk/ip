<?php
declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class StoreIpData extends Data
{
    public function __construct(
        public string $ip_address
    ) {}
}