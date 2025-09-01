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
            $query->byCountry($filters['country']);
        }

        if (!empty($filters['city'])) {
            $query->byCity($filters['city']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
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
            $geoData = $this->geoService->getGeoLocation($data->ip_address);
            
            $ipAddress->update([
                'ip_address' => $data->ip_address,
                'country' => $geoData->country,
                'city' => $geoData->city,
            ]);
        }

        return $ipAddress->refresh();
    }

    public function delete(int $id): bool
    {
        $ipAddress = IpAddress::findOrFail($id);
        return $ipAddress->delete();
    }
}