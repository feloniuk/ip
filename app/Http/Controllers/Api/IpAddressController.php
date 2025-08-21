<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\StoreIpData;
use App\Exceptions\GeoLocationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Resources\IpAddressCollection;
use App\Http\Resources\IpAddressResource;
use App\Http\Resources\IpResource;
use App\Models\IpAddress;
use App\Services\GeoLocationService;
use App\Services\IpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Контролер для управління IP адресами (CRUD операції)
 * Забезпечує повний функціонал для адміністрування IP адрес з геолокацією
 */
class IpAddressController extends Controller
{
    /**
     * Ін'єкція сервісу геолокації через конструктор
     */
    public function __construct(
        private readonly GeoLocationService $geoLocationService,
        protected readonly IpService $ipService,
    ) {
        // В новых версиях Laravel middleware настраивается в routes или через атрибуты
    }

    /**
     * Отримання списку IP адрес з пагінацією
     * GET /api/v1/ip-addresses
     */
    public function index(Request $request): JsonResponse
    {
//        // Перевіряємо дозволи
//        if (!$request->user()->can('view ip addresses')) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Access denied. You do not have permission to view IP addresses.',
//            ], Response::HTTP_FORBIDDEN);
//        }

        // Валідуємо параметри запиту
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'country' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'search' => 'sometimes|string|max:255',
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);

        // Будуємо запит з фільтрами
        $query = IpAddress::with('creator')
            ->when($validated['country'] ?? null, function ($q, string $country) {
                return $q->byCountry($country);
            })
            ->when($validated['city'] ?? null, function ($q, string $city) {
                return $q->byCity($city);
            })
            ->when($validated['search'] ?? null, function ($q, string $search) {
                return $q->where(function ($subQuery) use ($search): void {
                    $subQuery->where('ip_address', 'LIKE', "%{$search}%")
                        ->orWhere('country', 'LIKE', "%{$search}%")
                        ->orWhere('city', 'LIKE', "%{$search}%")
                        ->orWhere('isp', 'LIKE', "%{$search}%");
                });
            })
            ->latest('created_at');

        $ipAddresses = $query->paginate($perPage);

        Log::info('IP addresses list requested', [
            'user_id' => $request->user()?->id,
            'total' => $ipAddresses->total(),
            'filters' => array_intersect_key($validated, ['country', 'city', 'search']),
        ]);

        return response()->json([
            'success' => true,
            'data' => IpAddressResource::collection($ipAddresses->items()),
            'meta' => [
                'current_page' => $ipAddresses->currentPage(),
                'total' => $ipAddresses->total(),
                'per_page' => $ipAddresses->perPage(),
                'last_page' => $ipAddresses->lastPage(),
                'from' => $ipAddresses->firstItem(),
                'to' => $ipAddresses->lastItem(),
            ],
            'links' => [
                'first' => $ipAddresses->url(1),
                'last' => $ipAddresses->url($ipAddresses->lastPage()),
                'prev' => $ipAddresses->previousPageUrl(),
                'next' => $ipAddresses->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Створення нової IP адреси з отриманням геолокації
     * POST /api/v1/ip-addresses
     */
//    public function store(StoreIpAddressRequest $request): JsonResponse
//    {
//        try {
//            DB::beginTransaction();
//
//            $ipAddress = $request->validated('ip_address');
//
//            // Отримуємо геолокаційні дані
//            $geoData = $this->geoLocationService->getGeoLocation($ipAddress);
//
//            // Створюємо запис в базі даних
//            $ipRecord = IpAddress::create([
//                ...$geoData->toArray(),
//                'ip_address' => $ipAddress,
//                'created_by' => $request->user()->id,
//            ]);
//
//            // Завантажуємо зв'язок з користувачем для відповіді
//            $ipRecord->load('creator');
//
//            DB::commit();
//
//            Log::info('IP address created successfully', [
//                'ip_id' => $ipRecord->id,
//                'ip_address' => $ipAddress,
//                'location' => $geoData->getFormattedLocation(),
//                'user_id' => $request->user()->id,
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'IP address created successfully',
//                'data' => new IpAddressResource($ipRecord),
//            ], Response::HTTP_CREATED);
//
//        } catch (GeoLocationException $exception) {
//            DB::rollBack();
//
//            Log::warning('Failed to get geolocation for IP address', [
//                'ip_address' => $ipAddress ?? 'unknown',
//                'error' => $exception->getMessage(),
//                'user_id' => $request->user()->id,
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Failed to retrieve geolocation data',
//                'error' => $exception->getMessage(),
//                'code' => 'GEOLOCATION_ERROR',
//            ], Response::HTTP_UNPROCESSABLE_ENTITY);
//
//        } catch (\Throwable $exception) {
//            DB::rollBack();
//
//            Log::error('Failed to create IP address record', [
//                'ip_address' => $ipAddress ?? 'unknown',
//                'error' => $exception->getMessage(),
//                'user_id' => $request->user()->id,
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Internal server error occurred',
//                'error' => config('app.debug') ? $exception->getMessage() : 'Something went wrong',
//                'code' => 'INTERNAL_ERROR',
//            ], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }

    public function store(StoreIpAddressRequest $request): JsonResponse
    {
        $ip = $this->ipService->store(StoreIpData::from($request));

        return IpResource::make($ip);
    }

    /**
     * Отримання конкретної IP адреси
     * GET /api/v1/ip-addresses/{id}
     */
    public function show(Request $request, IpAddress $ipAddress): JsonResponse
    {
        // Перевіряємо дозволи
        if (!$request->user()->can('view ip addresses')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Завантажуємо зв'язок з користувачем
        $ipAddress->load('creator');

        Log::debug('IP address details requested', [
            'ip_id' => $ipAddress->id,
            'ip_address' => $ipAddress->ip_address,
        ]);

        return response()->json([
            'success' => true,
            'data' => new IpAddressResource($ipAddress),
        ]);
    }

    /**
     * Оновлення геолокаційних даних для IP адреси
     * PUT/PATCH /api/v1/ip-addresses/{id}
     */
    public function update(UpdateIpAddressRequest $request, IpAddress $ipAddress): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Очищаємо кеш якщо потрібно примусове оновлення
            if ($request->validated('force_refresh') === true) {
                $this->geoLocationService->clearCache($ipAddress->ip_address);
            }

            // Отримуємо свіжі геолокаційні дані
            $geoData = $this->geoLocationService->getGeoLocation($ipAddress->ip_address);

            // Оновлюємо запис
            $ipAddress->update($geoData->toArray());

            // Перезавантажуємо модель з новими даними
            $ipAddress->refresh()->load('creator');

            DB::commit();

            Log::info('IP address geolocation updated', [
                'ip_id' => $ipAddress->id,
                'ip_address' => $ipAddress->ip_address,
                'new_location' => $geoData->getFormattedLocation(),
                'force_refresh' => $request->validated('force_refresh'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'IP address geolocation updated successfully',
                'data' => new IpAddressResource($ipAddress),
            ]);

        } catch (GeoLocationException $exception) {
            DB::rollBack();

            Log::warning('Failed to update geolocation data', [
                'ip_id' => $ipAddress->id,
                'ip_address' => $ipAddress->ip_address,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update geolocation data',
                'error' => $exception->getMessage(),
                'code' => 'GEOLOCATION_ERROR',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to update IP address', [
                'ip_id' => $ipAddress->id,
                'ip_address' => $ipAddress->ip_address,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error occurred',
                'error' => config('app.debug') ? $exception->getMessage() : 'Something went wrong',
                'code' => 'INTERNAL_ERROR',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Видалення IP адреси
     * DELETE /api/v1/ip-addresses/{id}
     */
    public function destroy(Request $request, IpAddress $ipAddress): JsonResponse
    {
        // Перевіряємо дозволи
        if (!$request->user()->can('delete ip addresses')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $ipAddressValue = $ipAddress->ip_address;
            $ipId = $ipAddress->id;

            $ipAddress->delete();

            Log::info('IP address deleted successfully', [
                'ip_id' => $ipId,
                'ip_address' => $ipAddressValue,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'IP address deleted successfully',
            ]);

        } catch (\Throwable $exception) {
            Log::error('Failed to delete IP address', [
                'ip_id' => $ipAddress->id,
                'ip_address' => $ipAddress->ip_address,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete IP address',
                'error' => config('app.debug') ? $exception->getMessage() : 'Something went wrong',
                'code' => 'DELETE_ERROR',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
