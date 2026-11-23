<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Concerns\BuildsReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    use BuildsReport;

    public function index(Request $request): View
    {
        return view('manajemen.reports.index', $this->reportData($request));
    }

    public function export(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
