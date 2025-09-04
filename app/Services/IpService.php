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
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class IpService
{
    public function __construct(
        private readonly GeoLocationService $geoService,
        private readonly IpAddress $ipAddress
    ) {}

    public function store(StoreIpAddressRequest $request): IpAddress
    {
        $data = StoreIpData::from($request);
        $geoData = $this->geoService->getGeoLocation($data->ip_address);

        return IpAddress::create($geoData->toArray());
    }

    public function getAll(IndexIpAddressRequest $request): LengthAwarePaginator
    {
        $filters = IndexIpData::from($request);
        $query = new IpAddress();

        if ($filters->country) {
            $query = $query->byCountry($filters->country);
        }

        if ($filters->city) {
            $query = $query->byCity($filters->city);
        }

        if ($filters->search) {
            $query = $query->search($filters->search);
        }

        return $query->latest('created_at')->paginate($filters->per_page);
    }

    public function getById(int $id): IpAddress
    {
        $ipAddress = $this->ipAddress->find($id);

        if (!$ipAddress) {
            throw new ModelNotFoundException("IP address with ID {$id} not found");
        }

        return $ipAddress;
    }

    public function update(UpdateIpData $data): IpAddress
    {
        $ipAddress = $this->getById($data->id);

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
        return $this->getById($id)->delete();
    }
}