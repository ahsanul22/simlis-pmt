@extends('layouts.app')

@php $isEdit = $user->exists; @endphp

@section('title', $isEdit ? 'Ubah User' : 'Tambah User')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah User' : 'Tambah User'" :subtitle="$isEdit ? $user->name : 'Buat akun baru dan tentukan role-nya'"
               :back="route('super-admin.users.index')" />

<form method="POST" action="{{ $isEdit ? route('super-admin.users.update', $user) : route('super-admin.users.store') }}"
      x-data="{ role: @js(old('role', $user->role?->value ?? 'pic_branch')) }" class="grid gap-6 lg:grid-cols-3">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="space-y-6 lg:col-span-2">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Data Pengguna</h2></div>
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-form.input name="name" label="Nama lengkap" :value="$user->name" required /></div>
                <x-form.input name="username" label="Username" :value="$user->username" required hint="Huruf kecil, angka, titik" />
                <x-form.input type="email" name="email" label="Email" :value="$user->email" required hint="Tujuan notifikasi reminder" />
                <x-form.input name="nip" label="NIP / NIPP" :value="$user->nip" />
                <x-form.input name="phone" label="No. HP" :value="$user->phone" />
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title">Role & Akses</h2></div>
            <div class="space-y-5 p-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($roles as $r)
                        <label class="flex cursor-pointer gap-3 rounded-xl border-2 p-4 transition"
                               :class="role === '{{ $r->value }}' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/5' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="role" value="{{ $r->value }}" x-model="role" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-semibold">{{ $r->label() }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">
                                    @switch($r)
                                        @case(\App\Enums\UserRole::SUPER_ADMIN) Kelola user, master data & pengaturan sistem @break
                                        @case(\App\Enums\UserRole::ADMIN_TI) Kelola perangkat & lisensi semua lokasi, proses perpanjangan @break
                                        @case(\App\Enums\UserRole::MANAJEMEN) Pantau seluruh data & setujui pengajuan perpanjangan @break
                                        @case(\App\Enums\UserRole::PIC_BRANCH) Pantau lisensi branch sendiri & ajukan perpanjangan @break
                                    @endswitch
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('role')<p class="form-error">{{ $message }}</p>@enderror

                <div>
                    <label for="branch_id" class="form-label">Lokasi <span class="text-rose-500" x-show="role === 'pic_branch'">*</span></label>
                    <select id="branch_id" name="branch_id" class="form-control" :required="role === 'pic_branch'">
                        <option value="">— Tidak terikat lokasi —</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id', $user->branch_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint" x-show="role === 'pic_branch'">PIC Branch hanya dapat melihat data lokasi ini.</p>
                    @error('branch_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Keamanan</h2></div>
            <div class="space-y-4 p-5">
                <x-form.input type="password" name="password" :label="$isEdit ? 'Password baru' : 'Password'" :required="! $isEdit"
                              :hint="$isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'" autocomplete="new-password" />
                <x-form.input type="password" name="password_confirmation" label="Konfirmasi password" autocomplete="new-password" />
                <x-form.toggle name="is_active" label="Akun aktif" :checked="$user->is_active ?? true" hint="Akun nonaktif tidak dapat login" />
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('super-admin.users.index') }}" class="btn btn-secondary flex-1">Batal</a>
            <button type="submit" class="btn btn-primary flex-1">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
        </div>
    </div>
</form>
@endsection
