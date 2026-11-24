<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UserRequest;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->userData() + ['email_verified_at' => now()]);

        ActivityLog::record('user.created', $user, "Membuat akun {$user->name} ({$user->role->label()})", [
            'username' => $user->username,
            'role' => $user->role->value,
            'branch_id' => $user->branch_id,
        ]);

        return redirect()->route('super-admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->fill($request->userData());

        $changed = array_keys(Arr::except($user->getDirty(), ['password', 'remember_token']));
        $passwordChanged = $user->isDirty('password');

        if (! $changed && ! $passwordChanged) {
            return redirect()->route('super-admin.users.index')->with('info', 'Tidak ada perubahan pada akun '.$user->name.'.');
        }

        $old = Arr::only($user->getOriginal(), $changed);

        if ($passwordChanged || $user->isDirty('is_active')) {
            // Paksa sesi "ingat saya" di perangkat lain berakhir.
            $user->setRememberToken(Str::random(60));
        }

        $user->save();

        ActivityLog::record('user.updated', $user, "Mengubah akun {$user->name}", array_filter([
            'old' => $this->loggable($old),
            'new' => $this->loggable(Arr::only($user->getAttributes(), $changed)),
            'password_changed' => $passwordChanged ?: null,
        ]));

        return redirect()->route('super-admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui.".($passwordChanged ? ' Password diganti.' : ''));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === UserRole::SUPER_ADMIN && $user->is_active && ! User::otherActiveSuperAdminExists($user)) {
            return back()->with('error', 'Harus ada minimal satu Super Admin yang aktif.');
        }

        $user->delete(); // soft delete: riwayat (pembuat lisensi, pengaju perpanjangan, audit) tetap utuh

        ActivityLog::record('user.deleted', $user, "Menghapus akun {$user->name}", [
            'username' => $user->username,
            'role' => $user->role->value,
        ]);

        return redirect()->route('super-admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dihapus.");
    }

    /** Nilai mentah atribut → bentuk yang mudah dibaca di audit log. */
    private function loggable(array $attributes): array
    {
        return collect($attributes)->map(fn ($v, $k) => match ($k) {
            'is_active' => (bool) $v,
            'branch_id' => $v ? Branch::find($v)?->name ?? $v : null,
            default => $v instanceof \BackedEnum ? $v->value : $v,
        })->all();
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
