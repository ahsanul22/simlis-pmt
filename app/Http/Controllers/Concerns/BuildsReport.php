<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\AssetType;
use App\Enums\ExpiryStatus;
use App\Exports\LicenseReportExcel;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Vendor;
use App\Services\LicenseReport;
use App\Services\LicenseStatsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Halaman laporan + export Excel/PDF (Admin TI & Manajemen). */
trait BuildsReport
{
    protected function reportData(Request $request): array
    {
        $user = $request->user();
        $stats = LicenseStatsService::for($user);
        $report = LicenseReport::for($user, $request->query());

        return [
            'summary' => $stats->summary(),
            'statusChart' => $stats->statusChart(),
            'vendorChart' => $stats->vendorChart(),
            'timelineChart' => $stats->expiryTimelineChart(12),
            'byBranch' => $stats->byBranch(),
            'upcomingCost90' => $stats->upcomingCost(90),
            'renewalCounts' => $stats->renewalCounts(),
            'statuses' => ExpiryStatus::cases(),
            // Bagian RAB (mengikuti filter periode/vendor/lokasi)
            'report' => $report,
            'filters' => $report->filters,
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'branches' => $user->isBranchScoped() ? collect() : Branch::orderBy('name')->get(['id', 'name']),
            'assetTypes' => AssetType::cases(),
        ];
    }

    /** GET reports/export?format=xlsx|pdf&from_month=..&from_year=..&to_month=..&to_year=..&vendor=..&branch=.. */
    protected function exportReport(Request $request): Response
    {
        $format = $request->query('format', 'xlsx');
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);

        $user = $request->user();
        $report = LicenseReport::for($user, $request->query());
        $name = $report->fileName().'.'.$format;

        ActivityLog::record('report.exported', null, 'Mengunduh laporan lisensi ('.strtoupper($format).') — '.$report->period->label(), [
            'format' => $format,
            'filters' => $report->filters,
            'rows' => $report->licenses()->count(),
        ]);

        if ($format === 'pdf') {
            return Pdf::loadView('exports.license-report-pdf', ['report' => $report, 'printedBy' => $user->name])
                ->setPaper('a4', 'landscape')
                ->download($name);
        }

        $path = (new LicenseReportExcel($report, $user->name))->store();

        return response()->download($path, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
