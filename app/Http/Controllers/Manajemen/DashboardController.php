<?php

namespace App\Http\Controllers\Manajemen;

use App\Enums\RenewalStatus;
use App\Http\Controllers\Controller;
use App\Models\LicenseRenewal;
use App\Services\LicenseStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = LicenseStatsService::for($request->user());

        return view('manajemen.dashboard', [
            'summary' => $stats->summary(),
            'statusChart' => $stats->statusChart(),
            'vendorChart' => $stats->vendorChart(),
            'timelineChart' => $stats->expiryTimelineChart(12),
            'priorityLicenses' => $stats->priorityLicenses(6),
            'upcomingCost90' => $stats->upcomingCost(90),
            'renewalCounts' => $stats->renewalCounts(),
            'pendingApprovals' => LicenseRenewal::status(RenewalStatus::SUBMITTED)
                ->with(['license.branch:id,name', 'license.vendor:id,name', 'license.device.model:id,name', 'license.softwareProduct:id,name', 'requester:id,name'])
                ->oldest('submitted_at')->limit(6)->get(),
            'byBranch' => $stats->byBranch()->take(6),
        ]);
    }
}
