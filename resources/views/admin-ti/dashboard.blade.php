@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header :title="'Halo, '.auth()->user()->name" :subtitle="'Ringkasan lisensi seluruh lokasi · '.now()->translatedFormat('l, d F Y')">
    <x-slot:actions>
        <a href="{{ route('admin-ti.devices.create') }}" class="btn btn-secondary"><x-icon name="server-stack" class="h-4 w-4" /> Tambah Perangkat</a>
        <a href="{{ route('admin-ti.licenses.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Lisensi</a>
    </x-slot:actions>
</x-page-header>

@include('shared.dashboard._status-cards')

@if ($toComplete->isNotEmpty())
    <div class="card mt-6 border-brand-200 dark:border-brand-500/30">
        <div class="card-header bg-brand-50/60 dark:bg-brand-500/5">
            <div class="flex items-center gap-2">
                <x-icon name="check-badge" class="h-5 w-5 text-brand-600" />
                <h2 class="card-title">{{ $renewalCounts['approved'] }} pengajuan disetujui — menunggu input lisensi baru</h2>
            </div>
            <a href="{{ route('admin-ti.renewals.index', ['tab' => 'to_complete']) }}" class="text-sm font-medium text-brand-600 hover:underline">Lihat semua</a>
        </div>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($toComplete as $r)
                <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                    <div>
                        <p class="font-medium">{{ $r->license->device?->model->name ?? $r->license->softwareProduct?->name }} — {{ $r->license->branch->name }}</p>
                        <p class="text-xs text-slate-500">{{ $r->request_number }} · disetujui {{ tgl($r->reviewed_at) }}</p>
                    </div>
                    <a href="{{ route('admin-ti.renewals.complete-form', $r) }}" class="btn btn-primary btn-sm">Input Lisensi Baru</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2">
        @include('shared.dashboard._priority-table')
    </div>
    <div class="card">
        <div class="card-header"><h2 class="card-title">Komposisi Status</h2></div>
        <div class="card-body">
            <div class="h-64"><canvas x-data="chart(@js($statusChart))"></canvas></div>
            <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                <div><dt class="text-slate-500">Perangkat</dt><dd class="text-lg font-semibold">{{ $deviceCount }}</dd></div>
                <div><dt class="text-slate-500">Pengajuan berjalan</dt><dd class="text-lg font-semibold">{{ $renewalCounts['draft'] + $renewalCounts['submitted'] + $renewalCounts['approved'] }}</dd></div>
            </dl>
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Status per Vendor</h2></div>
        <div class="card-body"><div class="h-64"><canvas x-data="chart(@js($vendorChart))"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="card-title">Lisensi Berakhir per Bulan</h2><span class="text-xs text-slate-500">12 bulan ke depan</span></div>
        <div class="card-body"><div class="h-64"><canvas x-data="chart(@js($timelineChart))"></canvas></div></div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Perpanjangan Terakhir</h2>
        <a href="{{ route('admin-ti.renewals.index', ['tab' => 'history']) }}" class="text-sm font-medium text-brand-600 hover:underline">Riwayat lengkap</a>
    </div>
    @if ($recentRenewals->isEmpty())
        <x-empty-state icon="arrow-path" title="Belum ada perpanjangan selesai" />
    @else
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($recentRenewals as $r)
                <li class="flex flex-wrap items-center gap-4 px-5 py-3 text-sm">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"><x-icon name="check-circle" class="h-4 w-4" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium">{{ $r->license->device?->model->name ?? $r->license->softwareProduct?->name }} — {{ $r->license->branch->name }}</p>
                        <p class="text-xs text-slate-500">{{ $r->old_name }} → {{ $r->new_name }}</p>
                    </div>
                    <p class="text-xs text-slate-500">s/d {{ tgl($r->new_end_date) }}</p>
                    <a href="{{ route('admin-ti.renewals.show', $r) }}" class="btn btn-ghost btn-sm"><x-icon name="eye" class="h-4 w-4" /></a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
