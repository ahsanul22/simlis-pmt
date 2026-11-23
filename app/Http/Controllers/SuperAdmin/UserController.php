<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('branch:id,name')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%$t%")->orWhere('username', 'like', "%$t%")->orWhere('email', 'like', "%$t%")))
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->when($request->branch, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('name')
            ->paginate(per_page())
            ->withQueryString();

        return view('super-admin.users.index', [
            'users' => $users,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('super-admin.users.form', $this->formData(new User(['is_active' => true])));
    }

    public function edit(User $user): View
    {
        return view('super-admin.users.form', $this->formData($user));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        return $this->pendingBackend();
    }

    public function destroy(User $user): RedirectResponse
    {
        return $this->pendingBackend();
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'roles' => UserRole::cases(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
