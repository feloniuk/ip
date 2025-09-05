<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\GeoLocationData;
use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\DTOs\IndexIpData;
use App\DTOs\IdIpData;
use App\Models\IpAddress;
use App\Http\Requests\IndexIpAddressRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class IpService
{
    public function __construct(
        private readonly GeoLocationService $geoService,
        private readonly IpAddress $ipAddress
    ) {}

    public function store(StoreIpData $request): IpAddress
    {
        $existingIp = $this->ipAddress->where('ip_address', $request->ip_address)->first();
        if ($existingIp) {
            throw new \InvalidArgumentException("IP address already exists: {$request->ip_address}");
        }

        $geoData = $this->geoService->getGeoLocation($request->ip_address);

        return $this->ipAddress->query()->create($geoData->toArray());
    }

    public function getAll(IndexIpData $request): LengthAwarePaginator
    {
        $query = new IpAddress();

        if ($request->country) {
            $query = $query->byCountry($request->country);
        }

        if ($request->city) {
            $query = $query->byCity($request->city);
        }

        if ($request->search) {
            $query = $query->search($request->search);
        }

        return $query->latest('created_at')->paginate($request->per_page);
    }

    public function getById(IdIpData $data): IpAddress
    {
        $ipAddress = $this->ipAddress->find($data->id);

        if (!$ipAddress) {
            throw new ModelNotFoundException("IP address with ID {$data->id} not found");
        }

        return $ipAddress;
    }

    public function update(UpdateIpData $data): IpAddress
    {
        $ipAddress = $this->getById(new IdIpData($data->id));

        if ($data->ip_address && (($data->ip_address !== $ipAddress->ip_address) || (!$ipAddress->country || !$ipAddress->city))) {
            $geoData = $this->geoService->getGeoLocation($data->ip_address);

            $ipAddress->update([
                'ip_address' => $data->ip_address,
                'country' => $geoData->country,
                'city' => $geoData->city,
            ]);
        }

        if(!$ipAddress->country || !$ipAddress->city) {
            $geoData = $this->geoService->getGeoLocation($data->ip_address);
        }

        return $ipAddress->refresh();
    }

    public function delete(IdIpData $data): bool
    {
        $ipAddress = $this->getById(new IdIpData($data->id));
        return (bool) $ipAddress->delete();
    }
}