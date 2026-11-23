<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\ListsActivityLogs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    use ListsActivityLogs;

    public function index(Request $request): View
    {
        return view('super-admin.activity-logs.index', $this->activityLogData($request));
    }
}
