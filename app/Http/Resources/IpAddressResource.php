<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\IpAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IpAddress
 */
class IpAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'country' => $this->country,
            'city' => $this->city,
            'formatted_location' => $this->formatted_location,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}