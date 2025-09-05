<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\GeoLocationApiInterface;
use App\DTOs\GeoLocationData;
use App\Exceptions\GeoLocationException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Client\RequestException;

final readonly class GeoLocationApi implements GeoLocationApiInterface
{
    public function __construct(
        private HttpFactory $http,
        private ConfigRepository $config
    ) {}

    public function fetchGeoLocationData(string $ipAddress): GeoLocationData
    {
        $this->validateIpAddress($ipAddress);
        
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

            return new GeoLocationData(
                country: $data['country'] ?? null,
                city: $data['city'] ?? null,
                ip_address: $ipAddress
            );

        } catch (RequestException $e) {
            throw GeoLocationException::connectionError($e->getMessage());
        }
    }

    private function validateIpAddress(string $ipAddress): void
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }
    }
}