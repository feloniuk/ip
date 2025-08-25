<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\GeoLocationServiceInterface;
use App\DTOs\GeoLocationData;
use App\Exceptions\GeoLocationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class GeoLocationService implements GeoLocationServiceInterface
{
    public function getGeoLocation(string $ipAddress): GeoLocationData
    {
        $this->validateIpAddress($ipAddress);

        $cacheKey = Config::get('geolocation.cache.prefix') . $ipAddress;
        $cacheTtl = Config::get('geolocation.cache.ttl');
        
        return Cache::remember($cacheKey, $cacheTtl, function () use ($ipAddress) {
            return $this->fetchFromApi($ipAddress);
        });
    }

    private function validateIpAddress(string $ipAddress): void
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }
    }

    private function fetchFromApi(string $ipAddress): GeoLocationData
    {
        $apiUrl = Config::get('geolocation.api.url');
        $timeout = Config::get('geolocation.api.timeout');
        $retryAttempts = Config::get('geolocation.api.retry_attempts');
        $retryDelay = Config::get('geolocation.api.retry_delay');

        try {
            $response = Http::timeout($timeout)
                ->retry($retryAttempts, $retryDelay)
                ->get($apiUrl . $ipAddress);

            if (!$response->successful()) {
                throw GeoLocationException::apiError("HTTP {$response->status()}");
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'fail') {
                throw GeoLocationException::apiError($data['message'] ?? 'Unknown API error');
            }

            return GeoLocationData::fromApiResponse($data, $ipAddress);

        } catch (ConnectionException $e) {
            throw GeoLocationException::connectionError($e->getMessage());
        } catch (RequestException $e) {
            throw GeoLocationException::apiError($e->getMessage());
        }
    }
}