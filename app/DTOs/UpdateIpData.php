<?php
declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UpdateIpData extends Data
{
    public function __construct(
        public bool $force_refresh = false,
    ) {}
}