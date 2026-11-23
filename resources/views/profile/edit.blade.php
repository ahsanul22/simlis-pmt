@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<x-page-header title="Profil Saya" subtitle="Perbarui data diri dan password akun" />

<div class="grid gap-6 lg:grid-cols-3">
    <div class="card h-fit p-6 text-center">
        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-brand-600 text-2xl font-bold text-white">
            {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}
        </span>
        <p class="mt-4 text-lg font-semibold">{{ $user->name }}</p>
        <p class="text-sm text-slate-500">{{ '@'.$user->username }}</p>
        <div class="mt-3 flex justify-center"><x-badge :enum="$user->role" :dot="false" /></div>
        <dl class="mt-6 space-y-2 border-t border-slate-100 pt-4 text-left text-sm dark:border-slate-800">
            <div class="flex justify-between"><dt class="text-slate-500">Lokasi</dt><dd>{{ $user->branch?->name ?? '-' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Login terakhir</dt><dd>{{ tgl_waktu($user->last_login_at) }}</dd></div>
        </dl>
    </div>

    <div class="space-y-6 lg:col-span-2">
        <form method="POST" action="{{ route('profile.update') }}" class="card">
            @csrf
            @method('PUT')
            <div class="card-header"><h2 class="card-title">Data Diri</h2></div>
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-form.input name="name" label="Nama lengkap" :value="$user->name" required /></div>
                <x-form.input type="email" name="email" label="Email" :value="$user->email" required />
                <x-form.input name="phone" label="No. HP" :value="$user->phone" />
            </div>
            <div class="flex justify-end border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" class="card">
            @csrf
            @method('PUT')
            <div class="card-header"><h2 class="card-title">Ganti Password</h2></div>
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-form.input type="password" name="current_password" label="Password saat ini" required autocomplete="current-password" /></div>
                <x-form.input type="password" name="password" label="Password baru" required autocomplete="new-password" hint="Minimal 8 karakter" />
                <x-form.input type="password" name="password_confirmation" label="Konfirmasi password baru" required autocomplete="new-password" />
            </div>
            <div class="flex justify-end border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                <button class="btn btn-primary">Ganti Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
