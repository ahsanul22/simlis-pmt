<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SoftwareProduct;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoftwareProductController extends Controller
{
    public const CATEGORIES = ['Sistem Operasi', 'Office & Produktivitas', 'Antivirus / Endpoint', 'Database', 'Desain & Engineering', 'Backup', 'Virtualisasi', 'Lainnya'];

    public function index(Request $request): View
    {
        $products = SoftwareProduct::with('vendor:id,name')->withCount('licenses')
            ->when($request->q, fn ($q, $t) => $q->where('name', 'like', "%$t%"))
            ->when($request->vendor, fn ($q, $v) => $q->where('vendor_id', $v))
            ->orderBy('name')
            ->paginate(per_page())
            ->withQueryString();

        return view('super-admin.software-products.index', [
            'products' => $products,
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('super-admin.software-products.form', $this->formData(new SoftwareProduct(['is_active' => true])));
    }

    public function edit(SoftwareProduct $softwareProduct): View
    {
        return view('super-admin.software-products.form', $this->formData($softwareProduct));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, SoftwareProduct $softwareProduct): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(SoftwareProduct $softwareProduct): RedirectResponse
    {
        return $this->pendingBackend();
    }

    private function formData(SoftwareProduct $product): array
    {
        return [
            'product' => $product,
            'vendors' => Vendor::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => self::CATEGORIES,
        ];
    }
}
