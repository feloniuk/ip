<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\Models\IpAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IpServiceInterface
{
    public function store(StoreIpData $data): IpAddress;
    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getById(int $id): IpAddress;

    public function update(IpAddress $ipAddress, UpdateIpData $data): IpAddress;

    public function delete(int $id): bool;
}