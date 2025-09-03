<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\DTOs\IndexIpData;
use App\DTOs\GeoLocationData;
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
        private readonly IpAddress $ipModel,
        private readonly StoreIpData $ipStoreDto,
        private readonly IndexIpData $ipIndexDto,
        private readonly UpdateIpData $ipUpdateDto,
        private readonly GeoLocationData $GeoLocationDto
    ) {}

    public function store(StoreIpAddressRequest $request): IpAddress
    {
        $data = $this->ipStoreDto->from($request);
        $geoData = $this->geoService->getGeoLocation($data->ip_address);

        return $this->ipModel->create($geoData->toArray());
    }

    public function getAll(IndexIpAddressRequest $request): LengthAwarePaginator
    {
        $filters = $this->ipIndexDto->from($request);
        $query = $this->ipModel->filter($filters);

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
        $data = $this->ipUpdateDto->from($request);
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
        return $this->getById($id)->delete();
    }
}