<?php

namespace App\Http\Controllers\PicBranch;

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
        $user = $request->user();
        $stats = LicenseStatsService::for($user);

        return view('pic-branch.dashboard', [
            'summary' => $stats->summary(),
            'statusChart' => $stats->statusChart(),
            'priorityLicenses' => $stats->priorityLicenses(10),
            'deviceCount' => Device::visibleTo($user)->count(),
            'myRenewals' => LicenseRenewal::visibleTo($user)
                ->with(['license.device.model:id,name', 'license.softwareProduct:id,name'])
                ->latest('updated_at')->limit(5)->get(),
        ]);
    }
}
