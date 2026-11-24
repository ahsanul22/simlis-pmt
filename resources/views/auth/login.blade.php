@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="grid min-h-screen lg:grid-cols-2">
    {{-- Panel brand --}}
    <div class="relative hidden overflow-hidden bg-slate-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-brand-400/10 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white p-1.5">
                <img src="{{ asset('images/logo-pelindo-mark.png') }}" alt="Logo Pelindo" class="h-full w-full object-contain">
            </span>
            <div>
                <p class="font-bold tracking-wide text-white">{{ config('app.name') }}</p>
                <p class="text-xs text-slate-400">Sistem Monitoring Lisensi TI</p>
            </div>
        </div>

        <div class="relative max-w-md">
            <h1 class="text-3xl font-bold leading-tight text-white">Tidak ada lagi lisensi yang terlewat masa berlakunya.</h1>
            <p class="mt-4 text-slate-400">Pantau lisensi hardware & software di seluruh lokasi, dapatkan pengingat sejak H-90, dan kelola perpanjangan dengan alur persetujuan yang jelas.</p>

            <div class="mt-8 space-y-3">
                @foreach ([
                    ['check-circle', 'Aktif', 'Lebih dari 90 hari', 'text-emerald-400'],
                    ['clock', 'Akan Habis', 'Mulai proses pengadaan', 'text-amber-400'],
                    ['exclamation-triangle', 'Kritis', '30 hari terakhir', 'text-orange-400'],
                    ['x-circle', 'Expired', 'Segera ditindaklanjuti', 'text-rose-400'],
                ] as [$icon, $label, $desc, $color])
                    <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 px-4 py-3">
                        <x-icon :name="$icon" class="h-5 w-5 {{ $color }}" />
                        <span class="text-sm font-medium text-white">{{ $label }}</span>
                        <span class="ml-auto text-xs text-slate-400">{{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="relative text-xs text-slate-500">&copy; {{ now()->year }} Divisi Teknologi Informasi</p>
    </div>

    {{-- Form login --}}
    <div class="flex items-center justify-center px-4 py-12 sm:px-8">
        <div class="w-full max-w-sm" x-data="{ showPassword: false }">
            <div class="mb-8">
                <img src="{{ asset('images/logo-pelindo.png') }}" alt="Pelindo"
                     class="h-9 w-auto dark:rounded-lg dark:bg-white dark:px-3 dark:py-2 dark:h-12">
            </div>

            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Masuk ke akun Anda</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Gunakan username atau email yang terdaftar.</p>

            @include('includes.flash')

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf
                <x-form.input name="login" label="Username / Email" autofocus autocomplete="username" required x-ref="login" />

                <div>
                    <label for="password" class="form-label">Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                               class="form-control pr-10 @error('password') border-rose-400 @enderror">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600" aria-label="Tampilkan password">
                            <x-icon name="eye" class="h-4 w-4" x-show="!showPassword" />
                            <x-icon name="eye-slash" class="h-4 w-4" x-show="showPassword" x-cloak />
                        </button>
                    </div>
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800">
                    Ingat saya
                </label>

                <button type="submit" class="btn btn-primary w-full py-2.5">Masuk</button>
            </form>

            @if (app()->environment('local'))
                <div class="mt-8 rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Akun demo (password: <code>password</code>)</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach (['superadmin' => 'Super Admin', 'admin.ti' => 'Admin TI', 'manajemen' => 'Manajemen', 'pic.bima' => 'PIC Bima'] as $u => $label)
                            <button type="button" class="btn btn-secondary btn-sm"
                                    @click="$refs.login.value = '{{ $u }}'; document.getElementById('password').value = 'password'">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
