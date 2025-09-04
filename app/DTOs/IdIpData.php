<?php
declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class IdIpData extends Data
{
    public function __construct(
        public int $id
    ) {}
}