<?php

namespace App\Http\Controllers\PicBranch;

use App\Enums\DeviceStatus;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'vendor', 'status']);

        return view('pic-branch.devices.index', [
            'devices' => Device::visibleTo($request->user())
                ->filter($filters)
                ->with(['model.vendor:id,name', 'licenses' => fn ($q) => $q->active()])
                ->orderBy('serial_number')
                ->paginate(per_page())
                ->withQueryString(),
            'filters' => $filters,
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'statuses' => DeviceStatus::cases(),
        ]);
    }
}
