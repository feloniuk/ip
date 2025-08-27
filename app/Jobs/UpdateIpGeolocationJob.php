<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\GeoLocationServiceInterface;
use App\Models\IpAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateIpGeolocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1, 5, 15

    public function __construct(
        private readonly int $ipAddressId,
        private readonly bool $forceRefresh = false
    ) {
        $this->onQueue('geolocation');
    }

    public function handle(GeoLocationServiceInterface $geoService): void
    {
        $ipAddress = IpAddress::find($this->ipAddressId);

        if (!$ipAddress) {
            Log::warning("IP address with ID {$this->ipAddressId} not found for update");
            return;
        }

        try {
            $geoData = $geoService->getGeoLocation($ipAddress->ip_address);
            
            $ipAddress->update([
                'country' => $geoData->country,
                'city' => $geoData->city,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update geolocation for IP {$ipAddress->ip_address}: " . $e->getMessage(), [
                'ip_id' => $this->ipAddressId,
                'error' => $e->getMessage()
            ]);
            
            throw $e; // Позволяет джобу попробовать еще раз
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Update geolocation job failed permanently for IP ID {$this->ipAddressId}: " . $exception->getMessage());
    }
}