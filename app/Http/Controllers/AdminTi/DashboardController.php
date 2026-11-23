<?php

namespace App\Http\Controllers\AdminTi;

use App\Enums\RenewalStatus;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\LicenseRenewal;
use App\Services\LicenseStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = LicenseStatsService::for($request->user());

        return view('admin-ti.dashboard', [
            'summary' => $stats->summary(),
            'statusChart' => $stats->statusChart(),
            'vendorChart' => $stats->vendorChart(),
            'timelineChart' => $stats->expiryTimelineChart(12),
            'priorityLicenses' => $stats->priorityLicenses(8),
            'renewalCounts' => $stats->renewalCounts(),
            'deviceCount' => Device::count(),
            'toComplete' => LicenseRenewal::status(RenewalStatus::APPROVED)
                ->with(['license.branch:id,name', 'license.device.model:id,name', 'license.softwareProduct:id,name'])
                ->oldest('reviewed_at')->limit(5)->get(),
            'recentRenewals' => LicenseRenewal::status(RenewalStatus::COMPLETED)
                ->with(['license.branch:id,name', 'license.device.model:id,name', 'license.softwareProduct:id,name'])
                ->latest('completed_at')->limit(5)->get(),
        ]);
    }
}
