<?php

namespace App\Http\Controllers\AdminTi;

use App\Http\Controllers\Concerns\ListsActivityLogs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    use ListsActivityLogs;

    public function index(Request $request): View
    {
        return view('admin-ti.activity-logs.index', $this->activityLogData($request));
    }
}
