<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\IpAddressExport;
use App\DTOs\IndexIpData;
use App\Http\Requests\ExportIpAddressRequest;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Carbon;

final class ExportService
{
    public function __construct(
        private readonly ExcelWriter $excel
    ) {}

    public function exportIpAddresses(ExportIpAddressRequest $request): BinaryFileResponse
    {
        $filters = (new IndexIpData())->from($request);
        $fileName = 'ip-addresses-' . (new Carbon())->now()->format('Y-m-d-H-i-s') . '.xlsx';

        return $this->excel->download(
            new IpAddressExport($filters->toArray()),
            $fileName
        );
    }
}