<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeoLocationException extends Exception
{
    public function render(Request $request): JsonResponse|false
    {
        if (!$request->expectsJson()) {
            return false;
        }

        return response()->json([
            'success' => false,
            'message' => 'Geolocation service error',
            'error' => $this->getMessage(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function connectionError(string $details = ''): self
    {
        return new self("Connection error: {$details}", Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public static function invalidIpAddress(string $ip): self
    {
        return new self("Invalid IP address: {$ip}", Response::HTTP_BAD_REQUEST);
    }

    public static function apiError(string $message): self
    {
        return new self("API error: {$message}", Response::HTTP_BAD_GATEWAY);
    }
}