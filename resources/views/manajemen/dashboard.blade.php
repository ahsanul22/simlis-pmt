@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header :title="'Halo, '.auth()->user()->name" :subtitle="'Ringkasan eksekutif lisensi TI · '.now()->translatedFormat('l, d F Y')">
    <x-slot:actions>
        <a href="{{ route('manajemen.reports.index') }}" class="btn btn-secondary"><x-icon name="chart-bar" class="h-4 w-4" /> Laporan Lengkap</a>
    </x-slot:actions>
</x-page-header>

@include('shared.dashboard._status-cards')

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    {{-- Antrian persetujuan --}}
    <div class="card xl:col-span-2">
        <div class="card-header">
            <div>
                <h2 class="card-title">Menunggu Persetujuan Anda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pengajuan perpanjangan dari Admin TI & PIC Branch</p>
            </div>
            <a href="{{ route('manajemen.approvals.index') }}" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">Semua pengajuan</a>
        </div>
        @if ($pendingApprovals->isEmpty())
            <x-empty-state icon="check-badge" title="Tidak ada pengajuan yang menunggu">Semua pengajuan sudah ditinjau.</x-empty-state>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($pendingApprovals as $r)
                    <li class="flex flex-wrap items-center gap-4 px-5 py-3.5">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ $r->license->device?->model->name ?? $r->license->softwareProduct?->name }} — {{ $r->license->branch->name }}</p>
                            <p class="text-xs text-slate-500">{{ $r->request_number }} · {{ $r->requester?->name }} · {{ $r->submitted_at?->diffForHumans() }}</p>
                        </div>
                        <div class="text-right text-xs">
                            <p class="font-semibold">{{ rupiah($r->estimated_cost) }}</p>
                            <p class="text-slate-500">s/d {{ tgl($r->new_end_date) }}</p>
                        </div>
                        <a href="{{ route('manajemen.approvals.show', $r) }}" class="btn btn-primary btn-sm">Tinjau</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="space-y-6">
        <x-stat-card label="Estimasi Biaya 90 Hari" :value="rupiah($upcomingCost90)" icon="banknotes" tone="violet" hint="Lisensi yang berakhir dalam 90 hari" />
        <div class="card">
            <div class="card-header"><h2 class="card-title">Komposisi Status</h2></div>
            <div class="card-body"><div class="h-56"><canvas x-data="chart(@js($statusChart))"></canvas></div></div>
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Lisensi Berakhir per Bulan</h2><span class="text-xs text-slate-500">Perencanaan anggaran</span></div>
        <div class="card-body"><div class="h-64"><canvas x-data="chart(@js($timelineChart))"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="card-title">Lokasi yang Perlu Perhatian</h2></div>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($byBranch as $row)
                @php $attention = $row['warning'] + $row['critical'] + $row['expired']; @endphp
                <li class="flex items-center gap-4 px-5 py-3 text-sm">
                    <x-icon name="map-pin" class="h-4 w-4 text-slate-400" />
                    <span class="flex-1 font-medium">{{ $row['name'] }}</span>
                    <div class="flex gap-1.5">
                        @if ($row['expired'])<span class="badge bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300">{{ $row['expired'] }} expired</span>@endif
                        @if ($row['critical'])<span class="badge bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-300">{{ $row['critical'] }} kritis</span>@endif
                        @if ($row['warning'])<span class="badge bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300">{{ $row['warning'] }} akan habis</span>@endif
                        @if (! $attention)<span class="text-xs text-emerald-600">Aman · {{ $row['total'] }} lisensi</span>@endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="mt-6">
    @include('shared.dashboard._priority-table', ['title' => 'Lisensi Prioritas'])
</div>
@endsection
