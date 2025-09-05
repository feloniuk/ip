<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DTOs\UpdateIpData;
use App\DTOs\StoreIpData;
use App\DTOs\IndexIpData;
use App\DTOs\IdIpData;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Requests\IndexIpAddressRequest;
use App\Http\Requests\ExportIpAddressRequest;
use App\Http\Requests\DeleteIpAddressRequest;
use App\Http\Requests\ShowIpAddressRequest;
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
        $ipAddresses = $this->ipService->getAll(new IndexIpData($request->validated()));
        return IpAddressResource::collection($ipAddresses);
    }

    /**
     * POST /api/v1/ip-addresses
     */
    public function store(StoreIpAddressRequest $request): IpAddressResource
    {
        $ip = $this->ipService->store(new StoreIpData($request->get('ip_address')));
        return new IpAddressResource($ip);
    }

    /**
     * GET /api/v1/ip-addresses/{id}
     */
    public function show(ShowIpAddressRequest $request): IpAddressResource
    {
        $ipAddress = $this->ipService->getById($request->get('ip_address'));
        return new IpAddressResource($ipAddress);
    }

    /**
     * PUT/PATCH /api/v1/ip-addresses/{id}
     */
    public function update(UpdateIpAddressRequest $request, int $id): IpAddressResource
    {
        $updatedIp = $this->ipService->update(new UpdateIpData($request->get('ip_address'), $id));
        return new IpAddressResource($updatedIp);
    }

    /**
     * DELETE /api/v1/ip-addresses/{id}
     */
    public function destroy(DeleteIpAddressRequest $request): JsonResponse
    {
        $this->ipService->delete(
            new IdIpData($request->getValidatedId())
        );

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