<?php

namespace App\Http\Controllers\PicBranch;

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
        return view('pic-branch.renewals.index', $this->renewalIndexData($request, [
            'active' => [RenewalStatus::DRAFT, RenewalStatus::SUBMITTED, RenewalStatus::APPROVED],
            'completed' => [RenewalStatus::COMPLETED],
            'closed' => [RenewalStatus::REJECTED, RenewalStatus::CANCELLED],
        ], 'active'));
    }

    public function show(LicenseRenewal $renewal): View
    {
        return view('pic-branch.renewals.show', $this->renewalShowData($renewal));
    }

    public function create(License $license): View
    {
        $this->authorizeBranch($license);

        return view('pic-branch.renewals.create', ['license' => $license->load(['branch', 'vendor', 'device.model', 'softwareProduct', 'openRenewal'])]);
    }

    public function store(Request $request, License $license): RedirectResponse
    {
        $this->authorizeBranch($license);

        return $this->pendingBackend();
    }

    public function cancel(LicenseRenewal $renewal): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
