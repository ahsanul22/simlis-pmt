<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\DeviceCategory;
use App\Http\Controllers\Controller;
use App\Models\DeviceModel;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceModelController extends Controller
{
    public function index(Request $request): View
    {
        $models = DeviceModel::with('vendor:id,name')->withCount('devices')
            ->when($request->q, fn ($q, $t) => $q->where('name', 'like', "%$t%"))
            ->when($request->vendor, fn ($q, $v) => $q->where('vendor_id', $v))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->orderBy('vendor_id')->orderBy('name')
            ->paginate(per_page())
            ->withQueryString();

        return view('super-admin.device-models.index', [
            'models' => $models,
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'categories' => DeviceCategory::cases(),
        ]);
    }

    public function create(): View
    {
        return view('super-admin.device-models.form', $this->formData(new DeviceModel(['is_active' => true])));
    }

    public function edit(DeviceModel $deviceModel): View
    {
        return view('super-admin.device-models.form', $this->formData($deviceModel));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, DeviceModel $deviceModel): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(DeviceModel $deviceModel): RedirectResponse
    {
        return $this->pendingBackend();
    }

    private function formData(DeviceModel $model): array
    {
        return [
            'model' => $model,
            'vendors' => Vendor::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => DeviceCategory::cases(),
        ];
    }
}
