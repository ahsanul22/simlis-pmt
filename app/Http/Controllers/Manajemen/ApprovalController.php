<?php

namespace App\Http\Controllers\Manajemen;

use App\Enums\RenewalStatus;
use App\Http\Controllers\Concerns\ListsRenewals;
use App\Http\Controllers\Controller;
use App\Models\LicenseRenewal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    use ListsRenewals;

    public function index(Request $request): View
    {
        return view('manajemen.approvals.index', $this->renewalIndexData($request, [
            'pending' => [RenewalStatus::SUBMITTED],
            'approved' => [RenewalStatus::APPROVED, RenewalStatus::COMPLETED],
            'rejected' => [RenewalStatus::REJECTED],
        ], 'pending'));
    }

    public function show(LicenseRenewal $renewal): View
    {
        return view('manajemen.approvals.show', $this->renewalShowData($renewal));
    }

    public function approve(Request $request, LicenseRenewal $renewal): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function reject(Request $request, LicenseRenewal $renewal): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
