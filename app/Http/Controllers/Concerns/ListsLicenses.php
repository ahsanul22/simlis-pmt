<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\AssetType;
use App\Enums\ExpiryStatus;
use App\Enums\LicenseType;
use App\Models\Branch;
use App\Models\License;
use App\Models\Vendor;
use App\Support\ExpiryPeriod;
use Illuminate\Http\Request;

/**
 * Query daftar & detail lisensi, dipakai Admin TI, Manajemen, dan PIC Branch.
 * Cakupan data otomatis mengikuti user (scope visibleTo).
 */
trait ListsLicenses
{
    protected function licenseIndexData(Request $request): array
    {
        $user = $request->user();
        $filters = $request->only(['q', 'vendor', 'branch', 'asset_type', 'license_type', 'status', ...ExpiryPeriod::KEYS]);

        $base = License::query()->visibleTo($user)->active();

        $licenses = (clone $base)
            ->filter($filters)
            ->with(['vendor:id,name', 'branch:id,name', 'device.model:id,name', 'softwareProduct:id,name', 'openRenewal'])
            ->orderByRaw('case when end_date is null then 1 else 0 end')
            ->orderBy('end_date')
            ->paginate(per_page())
            ->withQueryString();

        // Jumlah per status untuk tab filter (tanpa filter status)
        $statusCounts = collect(ExpiryStatus::cases())->mapWithKeys(fn (ExpiryStatus $s) => [
            $s->value => (clone $base)->filter(array_merge($filters, ['status' => null]))->whereExpiryStatus($s)->count(),
        ]);

        return [
            'licenses' => $licenses,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'totalCount' => (clone $base)->filter(array_merge($filters, ['status' => null]))->count(),
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'branches' => $user->isBranchScoped() ? collect() : Branch::orderBy('name')->get(['id', 'name']),
            'assetTypes' => AssetType::cases(),
            'licenseTypes' => LicenseType::cases(),
        ];
    }

    protected function licenseShowData(License $license): array
    {
        $this->authorizeBranch($license);

        $license->load([
            'vendor', 'branch', 'device.model', 'softwareProduct', 'creator:id,name', 'updater:id,name',
            'attachments.uploader:id,name', 'openRenewal.requester:id,name',
            'renewals.requester:id,name', 'renewals.reviewer:id,name', 'renewals.completer:id,name',
        ]);

        return [
            'license' => $license,
            'siblingLicenses' => $license->device
                ? $license->device->licenses()->where('id', '!=', $license->id)->with('vendor:id,name')->get()
                : collect(),
        ];
    }
}
