<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = Vendor::withCount(['deviceModels', 'softwareProducts', 'licenses'])
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%$t%")->orWhere('code', 'like', "%$t%")))
            ->orderBy('name')
            ->paginate(per_page())
            ->withQueryString();

        return view('super-admin.vendors.index', ['vendors' => $vendors]);
    }

    public function create(): View
    {
        return view('super-admin.vendors.form', ['vendor' => new Vendor(['is_active' => true])]);
    }

    public function edit(Vendor $vendor): View
    {
        return view('super-admin.vendors.form', ['vendor' => $vendor]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
