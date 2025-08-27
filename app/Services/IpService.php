<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GeoLocationServiceInterface;
use App\Contracts\IpServiceInterface;
use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\Models\IpAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final readonly class IpService implements IpServiceInterface
{
    public function __construct(
        private IpAddress $model,
        private GeoLocationServiceInterface $geoService,
    ) {}

    public function store(StoreIpData $data): IpAddress
    {
        $geoData = $this->geoService->getGeoLocation($data->ip_address);
        
        return $this->model->query()->create($geoData->toArray());
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query()->with('creator');

        if (!empty($filters['country'])) {
            $query->byCountry($filters['country']);
        }

        if (!empty($filters['city'])) {
            $query->byCity($filters['city']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where('ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    public function getById(int $id): IpAddress
    {
        return $this->model->query()->with('creator')->findOrFail($id);
    }

    public function update(IpAddress $ipAddress, UpdateIpData $data): IpAddress
    {
        $geoData = $this->geoService->getGeoLocation($ipAddress->ip_address);

        $ipAddress->update($geoData->toArray());

        return $ipAddress->refresh()->load('creator');
    }

    public function delete(int $id): bool
    {
        return $this->model->query()->findOrFail($id)->delete();
    }
}