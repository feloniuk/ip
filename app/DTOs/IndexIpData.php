<?php
declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class IndexIpData extends Data
{
    public function __construct(
        public int $per_page = 15,
        public int $page = 1,
        public ?string $country = null,
        public ?string $city = null,
        public ?string $search = null
    ) {
    }
}