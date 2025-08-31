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
use App\Jobs\UpdateIpGeolocationJob;
use App\Models\IpAddress;
use App\Services\IpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IpAddressController extends Controller
{
    public function __construct(
        protected readonly IpService $ipService,
    ) {}

    /**
     * GET /api/v1/ip-addresses
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = IndexIpData::from($request);
        $ipAddresses = $this->ipService->getAll($data->toArray(), $data->per_page);

        return IpAddressResource::collection($ipAddresses);
    }

    /**
     * POST /api/v1/ip-addresses
     */
    public function store(StoreIpAddressRequest $request): IpAddressResource
    {
        $ip = $this->ipService->store(StoreIpData::from($request));

        return new IpAddressResource($ip);
    }

    /**
     * GET /api/v1/ip-addresses/{id}
     */
    public function show(IpAddress $ipAddress): IpAddressResource
    {
        $ip = $this->ipService->getById($ipAddress->id);

        return new IpAddressResource($ip);
    }

    /**
     * PUT/PATCH /api/v1/ip-addresses/{id}
     */
    public function update(UpdateIpAddressRequest $request, IpAddress $ipAddress): IpAddressResource
    {
        $data = UpdateIpData::from($request);

        if ($data->ip_address) {
            $ipAddress->update(['ip_address' => $data->ip_address]);
            UpdateIpGeolocationJob::dispatch($ipAddress->id);
            $ipAddress = $ipAddress->refresh();
        }

        return new IpAddressResource($ipAddress);
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