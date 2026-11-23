<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Device;
use App\Models\User;
use App\Models\Vendor;
use App\Services\LicenseStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = LicenseStatsService::for($request->user());
        $usersByRole = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('super-admin.dashboard', [
            'summary' => $stats->summary(),
            'statusChart' => $stats->statusChart(),
            'counts' => [
                'users' => User::count(),
                'users_inactive' => User::where('is_active', false)->count(),
                'branches' => Branch::count(),
                'vendors' => Vendor::count(),
                'devices' => Device::count(),
            ],
            'usersByRole' => collect(UserRole::cases())->map(fn ($r) => ['role' => $r, 'total' => (int) ($usersByRole[$r->value] ?? 0)]),
            'branchesWithoutPic' => Branch::where('type', '!=', 'kantor_pusat')
                ->whereDoesntHave('users', fn ($q) => $q->where('role', UserRole::PIC_BRANCH)->where('is_active', true))
                ->orderBy('name')->get(['id', 'name']),
            'recentActivities' => ActivityLog::with('user:id,name')->latest('created_at')->limit(8)->get(),
        ]);
    }
}
