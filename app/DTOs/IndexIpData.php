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

    public function mapToIndexIpData(array $data): IndexIpData
    {
        return new self(
            per_page: $data['per_page'] ?? 15,
            page: $data['page'] ?? 1,
            country: $data['country'] ?? null,
            city: $data['city'] ?? null,
            search: $data['search'] ?? null
        );
    }
}