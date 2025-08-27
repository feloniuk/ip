<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\GeoLocationApiInterface;
use App\Exceptions\GeoLocationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

final readonly class GeoLocationApi implements GeoLocationApiInterface
{
    public function fetchGeoLocationData(string $ipAddress): array
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

            return $data;

        } catch (ConnectionException $e) {
            throw GeoLocationException::connectionError($e->getMessage());
        } catch (RequestException $e) {
            throw GeoLocationException::apiError($e->getMessage());
        }
    }
}