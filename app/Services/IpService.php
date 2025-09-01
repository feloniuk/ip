<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\Models\IpAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IpService
{
    public function __construct(
        private GeoLocationService $geoService,
    ) {}

    public function store(StoreIpData $data): IpAddress
    {
        $geoData = $this->geoService->getGeoLocation($data->ip_address);

        return IpAddress::create($geoData->toArray());
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = IpAddress::query();

        if (!empty($filters['country'])) {
            $query->where('country', 'LIKE', "%{$filters['country']}%");
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', "%{$filters['city']}%");
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
        return IpAddress::findOrFail($id);
    }

    public function update(IpAddress $ipAddress, UpdateIpData $data): IpAddress
    {
        if ($data->ip_address && $data->ip_address !== $ipAddress->ip_address) {
            $ipAddress->ip_address = $data->ip_address;
            
            $geoData = $this->geoService->getGeoLocation($data->ip_address);
            
            $ipAddress->update($geoData->toArray());
        }

        return $ipAddress->refresh();
    }

    public function delete(int $id): bool
    {
        $ipAddress = IpAddress::findOrFail($id);
        return $ipAddress->delete();
    }
}