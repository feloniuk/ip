<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\GeoLocationData;
use App\Exceptions\GeoLocationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервіс для отримання геолокаційної інформації по IP адресам
 * Використовує зовнішній API ip-api.com з кешуванням та rate limiting
 */
class GeoLocationService
{
    /**
     * URL геолокаційного API
     */
    private const API_URL = 'http://ip-api.com/json/';

    /**
     * Час кешування результатів (1 година)
     */
    private const CACHE_TTL_SECONDS = 3600;

    /**
     * Максимальна кількість запитів за хвилину (безкоштовний план ip-api.com)
     */
    private const MAX_REQUESTS_PER_MINUTE = 45;

    /**
     * Ключ для збереження лічильника запитів в кеші
     */
    private const RATE_LIMIT_CACHE_KEY = 'geo_api_requests_count';

    /**
     * Таймаут запиту в секундах
     */
    private const REQUEST_TIMEOUT_SECONDS = 10;

    /**
     * Кількість повторних спроб при помилці
     */
    private const RETRY_ATTEMPTS = 3;

    /**
     * Затримка між повторними спробами (мілісекунди)
     */
    private const RETRY_DELAY_MS = 1000;

    /**
     * Отримує геолокаційні дані для IP адреси
     * 
     * @param string $ipAddress IP адреса для пошуку
     * @return GeoLocationData Геолокаційні дані
     * @throws GeoLocationException При помилках API або валідації
     */
    public function getGeoLocation(string $ipAddress): GeoLocationData
    {
        // Валідуємо IP адресу
        $this->validateIpAddress($ipAddress);

        // Перевіряємо rate limit
        $this->checkRateLimit();

        // Спробуємо отримати з кешу
        $cacheKey = $this->getCacheKey($ipAddress);
        
        /** @var GeoLocationData|null $cachedData */
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData instanceof GeoLocationData) {
            Log::debug('Geolocation data retrieved from cache', ['ip' => $ipAddress]);
            return $cachedData;
        }

        // Отримуємо свіжі дані з API
        $geoData = $this->fetchFromApi($ipAddress);

        // Зберігаємо в кеш
        Cache::put($cacheKey, $geoData, self::CACHE_TTL_SECONDS);

        Log::info('Geolocation data fetched from API', [
            'ip' => $ipAddress,
            'location' => $geoData->getFormattedLocation(),
        ]);

        return $geoData;
    }

    /**
     * Валідує IP адресу
     * 
     * @param string $ipAddress
     * @throws GeoLocationException При недійсній IP адресі
     */
    private function validateIpAddress(string $ipAddress): void
    {
        // Перевіряємо базовий формат IP
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw GeoLocationException::invalidIpAddress($ipAddress);
        }

        // Перевіряємо, що це публічна IP адреса
        if (!filter_var(
            $ipAddress, 
            FILTER_VALIDATE_IP, 
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        )) {
            throw GeoLocationException::invalidIpAddress(
                "{$ipAddress} (must be a public IP address)"
            );
        }
    }

    /**
     * Перевіряє rate limit для API запитів
     * 
     * @throws GeoLocationException При перевищенні ліміту
     */
    private function checkRateLimit(): void
    {
        $currentRequests = (int) Cache::get(self::RATE_LIMIT_CACHE_KEY, 0);
        
        if ($currentRequests >= self::MAX_REQUESTS_PER_MINUTE) {
            Log::warning('Geolocation API rate limit exceeded', [
                'current_requests' => $currentRequests,
                'limit' => self::MAX_REQUESTS_PER_MINUTE,
            ]);
            
            throw GeoLocationException::rateLimitExceeded();
        }
    }

    /**
     * Інкрементує лічильник запитів
     */
    private function incrementRateLimit(): void
    {
        $key = self::RATE_LIMIT_CACHE_KEY;
        $currentCount = (int) Cache::get($key, 0);
        Cache::put($key, $currentCount + 1, 60); // TTL 60 секунд
    }

    /**
     * Отримує дані з зовнішнього API
     * 
     * @param string $ipAddress
     * @return GeoLocationData
     * @throws GeoLocationException
     */
    private function fetchFromApi(string $ipAddress): GeoLocationData
    {
        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->retry(self::RETRY_ATTEMPTS, self::RETRY_DELAY_MS)
                ->get(self::API_URL . $ipAddress, [
                    'fields' => 'status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query'
                ]);

            if (!$response->successful()) {
                throw GeoLocationException::apiError(
                    "HTTP {$response->status()}: {$response->body()}"
                );
            }

            /** @var array<string, mixed> $data */
            $data = $response->json();

            // Валідуємо структуру відповіді
            $this->validateApiResponse($data);

            // Перевіряємо статус відповіді
            if (($data['status'] ?? '') === 'fail') {
                $errorMessage = $data['message'] ?? 'Unknown API error';
                throw GeoLocationException::apiError($errorMessage);
            }

            // Інкрементуємо лічильник після успішного запиту
            $this->incrementRateLimit();

            return GeoLocationData::fromApiResponse($data);

        } catch (ConnectionException $exception) {
            Log::error('Geolocation API connection failed', [
                'ip' => $ipAddress,
                'error' => $exception->getMessage(),
            ]);
            
            throw GeoLocationException::connectionError($exception->getMessage());

        } catch (RequestException $exception) {
            Log::error('Geolocation API request failed', [
                'ip' => $ipAddress,
                'error' => $exception->getMessage(),
            ]);
            
            throw GeoLocationException::apiError($exception->getMessage());
        }
    }

    /**
     * Валідує відповідь від API
     * 
     * @param array<string, mixed> $data
     * @throws GeoLocationException
     */
    private function validateApiResponse(array $data): void
    {
        if (!isset($data['status'])) {
            throw GeoLocationException::apiError('Invalid API response format: missing status field');
        }
    }

    /**
     * Генерує ключ для кешування
     */
    private function getCacheKey(string $ipAddress): string
    {
        return "geolocation:ip:{$ipAddress}";
    }

    /**
     * Очищає кеш для конкретної IP адреси
     */
    public function clearCache(string $ipAddress): bool
    {
        $cacheKey = $this->getCacheKey($ipAddress);
        return Cache::forget($cacheKey);
    }

    /**
     * Перевіряє доступність геолокаційного сервісу
     */
    public function isServiceAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get(self::API_URL . '8.8.8.8');
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }
}