<?php

namespace App\Http\Controllers\AdminTi;

use App\Enums\RenewalStatus;
use App\Http\Controllers\Concerns\ListsRenewals;
use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseRenewal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RenewalController extends Controller
{
    use ListsRenewals;

    public function index(Request $request): View
    {
        return view('admin-ti.renewals.index', $this->renewalIndexData($request, [
            'to_complete' => [RenewalStatus::APPROVED],
            'in_review' => [RenewalStatus::DRAFT, RenewalStatus::SUBMITTED],
            'history' => [RenewalStatus::COMPLETED],
            'closed' => [RenewalStatus::REJECTED, RenewalStatus::CANCELLED],
        ], 'to_complete'));
    }

    public function show(LicenseRenewal $renewal): View
    {
        return view('admin-ti.renewals.show', $this->renewalShowData($renewal));
    }

    public function create(License $license): View
    {
        return view('admin-ti.renewals.create', ['license' => $license->load(['branch', 'vendor', 'device.model', 'softwareProduct', 'openRenewal'])]);
    }

    public function completeForm(LicenseRenewal $renewal): View
    {
        return view('admin-ti.renewals.complete', $this->renewalShowData($renewal));
    }

    public function store(Request $request, License $license): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function complete(Request $request, LicenseRenewal $renewal): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function cancel(LicenseRenewal $renewal): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
