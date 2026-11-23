<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/** Query halaman audit log, dipakai oleh controller beberapa role. */
trait ListsActivityLogs
{
    protected function activityLogData(Request $request): array
    {
        $logs = ActivityLog::with('user:id,name,role')
            ->when($request->q, fn ($q, $term) => $q->where('description', 'like', "%{$term}%"))
            ->when($request->user, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->event, fn ($q, $e) => $q->where('event', 'like', $e.'%'))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest('created_at')
            ->paginate(per_page(25))
            ->withQueryString();

        return [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'events' => [
                'auth' => 'Login / Logout',
                'license' => 'Lisensi',
                'device' => 'Perangkat',
                'renewal' => 'Perpanjangan',
                'user' => 'Pengguna',
                'master' => 'Master Data',
                'setting' => 'Pengaturan',
            ],
        ];
    }
}
