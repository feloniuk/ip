<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GeoLocationException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

final class GeoLocationApiService
{
    public function fetchGeoLocationData(string $ipAddress): array
    {
        $url = Config::get('geolocation.api.url', 'http://ip-api.com/json/') . $ipAddress;
        $timeout = Config::get('geolocation.api.timeout', 10);
        $retryAttempts = Config::get('geolocation.api.retry_attempts', 3);

        try {
            $response = Http::timeout($timeout)
                ->retry($retryAttempts, 1000)
                ->get($url);

            if (!$response->successful()) {
                throw GeoLocationException::apiError('API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'fail') {
                throw GeoLocationException::apiError($data['message'] ?? 'API request failed');
            }

            return [
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'ip_address' => $ipAddress,
            ];

        } catch (RequestException $e) {
            throw GeoLocationException::connectionError($e->getMessage());
        }
    }
}