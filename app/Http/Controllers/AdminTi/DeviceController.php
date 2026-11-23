<?php

namespace App\Http\Controllers\AdminTi;

use App\Enums\DeviceStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'vendor', 'branch', 'model', 'status']);

        $devices = Device::filter($filters)
            ->with(['branch:id,name', 'model.vendor:id,name', 'licenses' => fn ($q) => $q->active()])
            ->orderBy('branch_id')
            ->paginate(per_page())
            ->withQueryString();

        return view('admin-ti.devices.index', [
            'devices' => $devices,
            'filters' => $filters,
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'models' => DeviceModel::orderBy('name')->get(['id', 'name']),
            'statuses' => DeviceStatus::cases(),
        ]);
    }

    public function show(Device $device): View
    {
        $device->load(['branch', 'model.vendor', 'licenses.vendor', 'licenses.openRenewal', 'attachments.uploader:id,name', 'creator:id,name']);

        return view('admin-ti.devices.show', ['device' => $device]);
    }

    public function create(): View
    {
        return view('admin-ti.devices.form', $this->formData(new Device(['status' => DeviceStatus::ACTIVE])));
    }

    public function edit(Device $device): View
    {
        return view('admin-ti.devices.form', $this->formData($device));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(Device $device): RedirectResponse
    {
        return $this->pendingBackend();
    }

    private function formData(Device $device): array
    {
        return [
            'device' => $device,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'modelsByVendor' => DeviceModel::with('vendor:id,name')->where('is_active', true)->orderBy('name')->get()
                ->groupBy(fn ($m) => $m->vendor->name),
            'statuses' => DeviceStatus::cases(),
        ];
    }
}
