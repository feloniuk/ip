<?php
declare(strict_types=1);

namespace App\Services;

use App\DTOs\StoreIpData;
use App\Models\IpAddress;

final readonly class IpService
{
    public function __construct(
        protected IpAddress $model,
    )
    {
    }

    public function store(StoreIpData $data): IpAddress
    {
        return $this->model->query()->create($data->toArray());
    }
}
