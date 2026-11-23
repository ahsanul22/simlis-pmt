<?php

namespace App\Http\Controllers\AdminTi;

use App\Enums\AssetType;
use App\Enums\LicenseType;
use App\Http\Controllers\Concerns\ListsLicenses;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Device;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    use ListsLicenses;

    public function index(Request $request): View
    {
        return view('admin-ti.licenses.index', $this->licenseIndexData($request));
    }

    public function show(License $license): View
    {
        return view('admin-ti.licenses.show', $this->licenseShowData($license));
    }

    public function create(Request $request): View
    {
        $license = new License([
            'asset_type' => AssetType::HARDWARE,
            'license_type' => LicenseType::SUBSCRIPTION,
            'currency' => 'IDR',
            'device_id' => $request->integer('device') ?: null,
        ]);

        return view('admin-ti.licenses.form', $this->formData($license));
    }

    public function edit(License $license): View
    {
        return view('admin-ti.licenses.form', $this->formData($license));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, License $license): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(License $license): RedirectResponse
    {
        return $this->pendingBackend();
    }

    private function formData(License $license): array
    {
        return [
            'license' => $license,
            'assetTypes' => AssetType::cases(),
            'licenseTypes' => LicenseType::cases(),
            'vendors' => Vendor::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'devices' => Device::with(['model:id,name,vendor_id', 'branch:id,name'])->orderBy('branch_id')->get()
                ->map(fn ($d) => [
                    'id' => $d->id,
                    'label' => "{$d->model->name} — {$d->branch->name} (SN {$d->serial_number})",
                    'vendor_id' => $d->model->vendor_id,
                    'branch_id' => $d->branch_id,
                ]),
            'softwareProducts' => SoftwareProduct::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'edition', 'vendor_id'])
                ->map(fn ($s) => ['id' => $s->id, 'label' => trim("{$s->name} {$s->edition}"), 'vendor_id' => $s->vendor_id]),
        ];
    }
}
