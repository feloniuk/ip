<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Requests\IndexIpAddressRequest;
use App\Http\Requests\ExportIpAddressRequest;
use App\Http\Resources\IpAddressResource;
use App\Services\IpService;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Contracts\Routing\ResponseFactory;

class IpAddressController extends Controller
{
    public function __construct(
        private readonly IpService $ipService,
        private readonly ExportService $exportService,
        private readonly ResponseFactory $response
    ) {}

    /**
     * GET /api/v1/ip-addresses
     */
    public function index(IndexIpAddressRequest $request): AnonymousResourceCollection
    {
        $ipAddresses = $this->ipService->getAll($request);
        return IpAddressResource::collection($ipAddresses);
    }

    /**
     * POST /api/v1/ip-addresses
     */
    public function store(StoreIpAddressRequest $request): IpAddressResource
    {
        $ip = $this->ipService->store($request);
        return new IpAddressResource($ip);
    }

    /**
     * GET /api/v1/ip-addresses/{id}
     */
    public function show(int $id): IpAddressResource
    {
        $ipAddress = $this->ipService->getById($id);
        return new IpAddressResource($ipAddress);
    }

    /**
     * PUT/PATCH /api/v1/ip-addresses/{id}
     */
    public function update(UpdateIpAddressRequest $request, int $id): IpAddressResource
    {
        $updatedIp = $this->ipService->update($id, $request);
        return new IpAddressResource($updatedIp);
    }

    /**
     * DELETE /api/v1/ip-addresses/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $this->ipService->delete($id);

        return $this->response->json([
            'success' => true,
            'message' => 'IP address deleted successfully',
        ]);
    }

    /**
     * GET /api/v1/ip-addresses/export
     */
    public function export(ExportIpAddressRequest $request): BinaryFileResponse
    {
        return $this->exportService->exportIpAddresses($request);
    }
}