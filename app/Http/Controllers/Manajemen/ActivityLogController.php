<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Concerns\ListsActivityLogs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    use ListsActivityLogs;

    public function index(Request $request): View
    {
        return view('manajemen.activity-logs.index', $this->activityLogData($request));
    }
}
