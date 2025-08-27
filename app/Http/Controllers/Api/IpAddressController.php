<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\IndexIpData;
use App\DTOs\StoreIpData;
use App\DTOs\UpdateIpData;
use App\Exports\IpAddressExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Resources\IpAddressResource;
use App\Http\Resources\IpResource;
use App\Models\IpAddress;
use App\Services\GeoLocationService;
use App\Services\IpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Jobs\UpdateIpGeolocationJob;

class IpAddressController extends Controller
{

    public function __construct(
        private readonly GeoLocationService $geoLocationService,
        protected readonly IpService $ipService,
    ) {
        // В новых версиях Laravel middleware настраивается в routes или через атрибуты
    }

    /**
     * GET /api/v1/ip-addresses
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = IndexIpData::from($request);
        $ipAddresses = $this->ipService->getAll($data->toArray());

        return IpAddressResource::collection($ipAddresses);
    }

    /**
     * POST /api/v1/ip-addresses
     */
    public function store(StoreIpAddressRequest $request): AnonymousResourceCollection
    {
        $ip = $this->ipService->store(StoreIpData::from($request));

        return IpResource::collection($ip);
    }

    /**
     * GET /api/v1/ip-addresses/{id}
     */
    public function show(IpAddress $ipAddress): AnonymousResourceCollection
    {
        $ip = $this->ipService->getById($ipAddress->id);

        return IpAddressResource::collection($ip);
    }

    /**
     * PUT/PATCH /api/v1/ip-addresses/{ip_address}
     */
    public function update(UpdateIpAddressRequest $request, IpAddress $ipAddress): IpAddress
    {
        $data = UpdateIpData::from($request);

        // Start Job
        UpdateIpGeolocationJob::dispatch($ipAddress->id, $data);

        return $ipAddress->refresh()->load('creator');
        
        // $updatedIp = $this->ipService->update($ipAddress, $data);

        // return IpAddressResource::collection($updatedIp);
    }

    /**
     * DELETE /api/v1/ip-addresses/{id}
     */
    public function destroy(IpAddress $ipAddress): JsonResponse
    {
        $this->ipService->delete($ipAddress->id);

        return response()->json([
            'success' => true,
            'message' => 'IP address deleted successfully',
        ]);
    }

    /**
     * GET /api/v1/ip-addresses/export
     */
    public function export(Request $request): BinaryFileResponse
    {
        $data = IndexIpData::from($request);
        $filename = 'ip-addresses-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(
            new IpAddressExport($data->toArray()), 
            $filename
        );
    }
}
