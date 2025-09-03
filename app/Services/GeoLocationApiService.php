<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GeoLocationException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

final class GeoLocationApiService
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly ConfigRepository $config
    ) {}

    public function fetchGeoLocationData(string $ipAddress): array
    {
        $url = $this->config->get('geolocation.api.url', 'http://ip-api.com/json/') . $ipAddress;
        $timeout = $this->config->get('geolocation.api.timeout', 10);
        $retryAttempts = $this->config->get('geolocation.api.retry_attempts', 3);

        try {
            $response = $this->http->timeout($timeout)
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