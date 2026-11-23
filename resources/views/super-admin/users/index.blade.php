@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<x-page-header title="Manajemen User" subtitle="Kelola akun dan hak akses pengguna">
    <x-slot:actions>
        <a href="{{ route('super-admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah User</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4" x-data>
    <div class="relative">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nama, username, email…" class="form-control pl-9">
    </div>
    <select name="role" class="form-control" @change="$el.form.submit()">
        <option value="">Semua role</option>
        @foreach (\App\Enums\UserRole::cases() as $r)<option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->label() }}</option>@endforeach
    </select>
    <select name="branch" class="form-control" @change="$el.form.submit()">
        <option value="">Semua lokasi</option>
        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected(request('branch') == $b->id)>{{ $b->name }}</option>@endforeach
    </select>
    <select name="active" class="form-control" @change="$el.form.submit()">
        <option value="">Semua status</option>
        <option value="1" @selected(request('active') === '1')>Aktif</option>
        <option value="0" @selected(request('active') === '0')>Nonaktif</option>
    </select>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Pengguna</th><th>Role</th><th>Lokasi</th><th>Login Terakhir</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}
                                </span>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ '@'.$user->username }} · {{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td><x-badge :enum="$user->role" :dot="false" /></td>
                        <td>{{ $user->branch?->name ?? '-' }}</td>
                        <td class="text-xs">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}</td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300">Aktif</span>
                            @else
                                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('super-admin.users.edit', $user) }}" class="btn btn-ghost btn-sm" title="Ubah"><x-icon name="pencil" class="h-4 w-4" /></a>
                            @unless ($user->is(auth()->user()))
                                <x-delete-button :action="route('super-admin.users.destroy', $user)" :item="'user '.$user->name" />
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="users" title="Tidak ada pengguna" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->total() > min(PER_PAGE_OPTIONS))<div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $users->links() }}</div>@endif
</div>
@endsection
