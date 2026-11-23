<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\RenewalStatus;
use App\Models\LicenseRenewal;
use Illuminate\Http\Request;

/** Query daftar & detail pengajuan perpanjangan, dipakai beberapa role. */
trait ListsRenewals
{
    /**
     * @param  array<string, array<RenewalStatus>>  $tabs  key tab => status yang termasuk
     */
    protected function renewalIndexData(Request $request, array $tabs, string $defaultTab): array
    {
        $tab = array_key_exists($request->tab, $tabs) ? $request->tab : $defaultTab;
        $base = LicenseRenewal::visibleTo($request->user());

        $renewals = (clone $base)
            ->status(...$tabs[$tab])
            ->when($request->q, function ($q, $t) {
                $q->where(fn ($w) => $w->where('request_number', 'like', "%$t%")
                    ->orWhereHas('license', fn ($l) => $l->where('code', 'like', "%$t%")->orWhere('name', 'like', "%$t%")
                        ->orWhereHas('branch', fn ($b) => $b->where('name', 'like', "%$t%"))
                        ->orWhereHas('device', fn ($d) => $d->where('serial_number', 'like', "%$t%"))));
            })
            ->with(['license.branch:id,name', 'license.vendor:id,name', 'license.device.model:id,name', 'license.softwareProduct:id,name', 'requester:id,name'])
            ->latest('updated_at')
            ->paginate(per_page())
            ->withQueryString();

        return [
            'renewals' => $renewals,
            'tab' => $tab,
            'tabCounts' => collect($tabs)->map(fn ($statuses) => (clone $base)->status(...$statuses)->count()),
        ];
    }

    protected function renewalShowData(LicenseRenewal $renewal): array
    {
        $renewal->load([
            'license.branch', 'license.vendor', 'license.device.model', 'license.softwareProduct',
            'requester:id,name,role', 'reviewer:id,name,role', 'completer:id,name,role',
            'attachments.uploader:id,name',
        ]);

        $this->authorizeBranch($renewal->license);

        return ['renewal' => $renewal, 'license' => $renewal->license];
    }
}
