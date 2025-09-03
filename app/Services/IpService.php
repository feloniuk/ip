<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\DTOs\IndexIpData;
use App\Models\IpAddress;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Requests\IndexIpAddressRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class IpService
{
    public function __construct(
        private readonly GeoLocationService $geoService,
        private readonly IpAddress $ipModel
    ) {}

    public function store(StoreIpAddressRequest $request): IpAddress
    {
        $data = StoreIpData::from($request);
        $geoData = $this->geoService->getGeoLocation($data->ip_address);

        return $this->ipModel->create($geoData->toArray());
    }

    public function getAll(IndexIpAddressRequest $request): LengthAwarePaginator
    {
        $filters = IndexIpData::from($request);
        $query = $this->buildQuery($filters);

        return $query->latest('created_at')->paginate($filters->per_page);
    }

    public function getById(int $id): IpAddress
    {
        $ipAddress = $this->ipModel->find($id);

        if (!$ipAddress) {
            throw new ModelNotFoundException("IP address with ID {$id} not found");
        }

        return $ipAddress;
    }

    public function update(int $id, UpdateIpAddressRequest $request): IpAddress
    {
        $data = UpdateIpData::from($request);
        $ipAddress = $this->getById($id);

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

    public function delete(int $id): int
    {
        $ipAddress = $this->getById($id);
        return $ipAddress->delete();
    }

    private function buildQuery(IndexIpData $filters): Builder
    {
        $query = $this->ipModel->newQuery();

        if ($filters->country) {
            $query->byCountry($filters->country);
        }

        if ($filters->city) {
            $query->byCity($filters->city);
        }

        if ($filters->search) {
            $query->search($filters->search);
        }

        return $query;
    }
}