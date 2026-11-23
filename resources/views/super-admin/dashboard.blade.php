@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header :title="'Halo, '.auth()->user()->name" subtitle="Ringkasan sistem, pengguna, dan master data">
    <x-slot:actions>
        <a href="{{ route('super-admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah User</a>
    </x-slot:actions>
</x-page-header>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card label="Pengguna" :value="$counts['users']" icon="users" tone="violet" :href="route('super-admin.users.index')"
                 :hint="$counts['users_inactive'] ? $counts['users_inactive'].' akun nonaktif' : 'Semua akun aktif'" />
    <x-stat-card label="Lokasi" :value="$counts['branches']" icon="building-office" :href="route('super-admin.branches.index')" />
    <x-stat-card label="Vendor" :value="$counts['vendors']" icon="tag" tone="slate" :href="route('super-admin.vendors.index')" />
    <x-stat-card label="Lisensi Terpantau" :value="$summary['total']" icon="key" tone="success" :hint="$summary['attention'].' butuh perhatian'" />
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Pengguna per Role</h2></div>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($usersByRole as $row)
                <li>
                    <a href="{{ route('super-admin.users.index', ['role' => $row['role']->value]) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <x-badge :enum="$row['role']" />
                        <span class="font-semibold">{{ $row['total'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Branch Tanpa PIC</h2>
            <span class="badge {{ $branchesWithoutPic->isEmpty() ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">{{ $branchesWithoutPic->count() }}</span>
        </div>
        @if ($branchesWithoutPic->isEmpty())
            <x-empty-state icon="check-circle" title="Semua branch sudah punya PIC" />
        @else
            <div class="max-h-72 overflow-y-auto p-5">
                <p class="mb-3 text-xs text-slate-500">Reminder lisensi untuk branch ini hanya terkirim ke Admin TI. Tambahkan akun PIC Branch.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($branchesWithoutPic as $b)
                        <span class="badge bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-300">{{ $b->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Status Lisensi</h2></div>
        <div class="card-body"><div class="h-60"><canvas x-data="chart(@js($statusChart))"></canvas></div></div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Aktivitas Terbaru</h2>
        <a href="{{ route('super-admin.activity-logs.index') }}" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">Audit log</a>
    </div>
    @if ($recentActivities->isEmpty())
        <x-empty-state icon="clipboard-list" title="Belum ada aktivitas" />
    @else
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($recentActivities as $a)
                <li class="flex items-center gap-4 px-5 py-3 text-sm">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800"><x-icon name="clock" class="h-4 w-4" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate"><span class="font-medium">{{ $a->user?->name ?? 'Sistem' }}</span> — {{ $a->description ?? $a->event }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-500">{{ $a->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
