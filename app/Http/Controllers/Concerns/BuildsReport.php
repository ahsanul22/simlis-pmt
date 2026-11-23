<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\ExpiryStatus;
use App\Models\License;
use App\Services\LicenseStatsService;
use Illuminate\Http\Request;

/** Data halaman laporan (Admin TI & Manajemen). */
trait BuildsReport
{
    protected function reportData(Request $request): array
    {
        $stats = LicenseStatsService::for($request->user());

        return [
            'summary' => $stats->summary(),
            'statusChart' => $stats->statusChart(),
            'vendorChart' => $stats->vendorChart(),
            'timelineChart' => $stats->expiryTimelineChart(12),
            'byBranch' => $stats->byBranch(),
            'upcomingCost90' => $stats->upcomingCost(90),
            'renewalCounts' => $stats->renewalCounts(),
            'expiringSoon' => License::visibleTo($request->user())->active()
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<=', now()->addDays(90))
                ->with(['vendor:id,name', 'branch:id,name', 'device.model:id,name', 'softwareProduct:id,name'])
                ->orderBy('end_date')
                ->get(),
            'statuses' => ExpiryStatus::cases(),
        ];
    }
}
