<?php

namespace App\Http\Controllers\AdminTi;

use App\Http\Controllers\Concerns\BuildsReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    use BuildsReport;

    public function index(Request $request): View
    {
        return view('admin-ti.reports.index', $this->reportData($request));
    }

    public function export(Request $request): Response
    {
        return $this->exportReport($request);
    }
}
