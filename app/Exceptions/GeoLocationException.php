<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Виключення для помилок геолокаційного сервісу
 * Забезпечує коректну обробку помилок при роботі з зовнішнім API
 */
class GeoLocationException extends Exception
{
    /**
     * Конструктор виключення
     * 
     * @param string $message Повідомлення про помилку
     * @param int $code Код помилки
     * @param Exception|null $previous Попереднє виключення
     */
    public function __construct(
        string $message = 'Geolocation service error',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Визначає, чи потрібно логувати це виключення
     * Повертає true для логування в системі моніторингу
     */
    public function report(): bool
    {
        return true;
    }

    /**
     * Рендерить виключення в HTTP відповідь
     * 
     * @param Request $request
     * @return JsonResponse|false
     */
    public function render(Request $request): JsonResponse|false
    {
        // Обробляємо тільки JSON запити (API)
        if (!$request->expectsJson()) {
            return false;
        }

        return response()->json([
            'success' => false,
            'message' => 'Geolocation service error',
            'error' => $this->getMessage(),
            'code' => 'GEOLOCATION_ERROR',
            'timestamp' => now()->toISOString(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Створює виключення для помилки з'єднання
     */
    public static function connectionError(string $details = ''): self
    {
        $message = 'Unable to connect to geolocation service';
        if (!empty($details)) {
            $message .= ": {$details}";
        }
        
        return new self($message, Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * Створює виключення для перевищення ліміту запитів
     */
    public static function rateLimitExceeded(): self
    {
        return new self(
            'Rate limit exceeded. Please try again later.',
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }

    /**
     * Створює виключення для недійсної IP адреси
     */
    public static function invalidIpAddress(string $ipAddress): self
    {
        return new self(
            "Invalid IP address: {$ipAddress}",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Створює виключення для помилки API
     */
    public static function apiError(string $message): self
    {
        return new self(
            "API error: {$message}",
            Response::HTTP_BAD_GATEWAY
        );
    }
}