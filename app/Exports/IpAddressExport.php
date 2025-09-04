<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\IpAddress;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Database\Eloquent\Builder;

class IpAddressExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly array $filters = []
    ) {}

    public function query()
    {
        $query = IpAddress::query();

        // Применяем те же фильтры, что и в IpService
        if (!empty($this->filters['country'])) {
            $query->byCountry($this->filters['country']);
        }

        if (!empty($this->filters['city'])) {
            $query->byCity($this->filters['city']);
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $search = $this->filters['search'];
                $q->where('ip_address', 'LIKE', "%{$search}%")
                  ->orWhere('country', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'IP Address',
            'Country',
            'City',
            'Location',
            'Created At',
            'Updated At',
        ];
    }

    public function map($ipAddress): array
    {
        return [
            $ipAddress->id,
            $ipAddress->ip_address,
            $ipAddress->country ?? 'N/A',
            $ipAddress->city ?? 'N/A'
        ];
    }
}