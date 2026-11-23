<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\BranchType;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::withCount(['devices', 'licenses', 'users'])
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%$t%")->orWhere('code', 'like', "%$t%")))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->orderByRaw("case when type = 'kantor_pusat' then 0 else 1 end")
            ->orderBy('name')
            ->paginate(per_page())
            ->withQueryString();

        return view('super-admin.branches.index', ['branches' => $branches, 'types' => BranchType::cases()]);
    }

    public function create(): View
    {
        return view('super-admin.branches.form', ['branch' => new Branch(['is_active' => true]), 'types' => BranchType::cases()]);
    }

    public function edit(Branch $branch): View
    {
        return view('super-admin.branches.form', ['branch' => $branch, 'types' => BranchType::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        return $this->pendingBackend();
    }
}
