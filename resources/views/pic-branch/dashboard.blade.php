@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php $branch = auth()->user()->branch; @endphp

<x-page-header :title="'Branch '.($branch?->name ?? '-')" :subtitle="'Halo, '.auth()->user()->name.' · '.now()->translatedFormat('l, d F Y')" />

@if (! $branch)
    <div class="card mb-6 flex items-start gap-3 border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
        <x-icon name="exclamation-triangle" class="h-5 w-5 shrink-0" />
        Akun Anda belum ditautkan ke branch mana pun. Hubungi Super Admin.
    </div>
@endif

@if ($summary['attention'] > 0)
    <div class="mb-6 flex flex-wrap items-center gap-4 rounded-xl bg-gradient-to-r from-rose-600 to-orange-500 p-5 text-white shadow-card">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20"><x-icon name="exclamation-triangle" class="h-6 w-6" /></span>
        <div class="flex-1">
            <p class="font-semibold">{{ $summary['attention'] }} lisensi di branch Anda perlu ditindaklanjuti</p>
            <p class="text-sm text-white/80">{{ $summary['expired'] }} expired · {{ $summary['critical'] }} kritis · {{ $summary['warning'] }} akan habis. Ajukan perpanjangan agar layanan tidak terganggu.</p>
        </div>
        <a href="{{ route('pic-branch.licenses.index', ['status' => $summary['expired'] ? 'expired' : ($summary['critical'] ? 'critical' : 'warning')]) }}" class="btn bg-white text-rose-600 hover:bg-rose-50">Lihat lisensi</a>
    </div>
@endif

@include('shared.dashboard._status-cards')

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="xl:col-span-2">
        @include('shared.dashboard._priority-table')
    </div>
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Komposisi Status</h2></div>
            <div class="card-body"><div class="h-56"><canvas x-data="chart(@js($statusChart))"></canvas></div></div>
        </div>
        <x-stat-card label="Perangkat di Branch" :value="$deviceCount" icon="server-stack" tone="slate" :href="route('pic-branch.devices.index')" />
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <h2 class="card-title">Pengajuan Saya</h2>
        <a href="{{ route('pic-branch.renewals.index') }}" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">Lihat semua</a>
    </div>
    @if ($myRenewals->isEmpty())
        <x-empty-state icon="arrow-path" title="Belum ada pengajuan">Ajukan perpanjangan dari halaman detail lisensi.</x-empty-state>
    @else
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($myRenewals as $r)
                <li>
                    <a href="{{ route('pic-branch.renewals.show', $r) }}" class="flex flex-wrap items-center gap-4 px-5 py-3 text-sm hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <span class="font-mono text-xs">{{ $r->request_number }}</span>
                        <span class="flex-1 font-medium">{{ $r->license->device?->model->name ?? $r->license->softwareProduct?->name }}</span>
                        <span class="text-xs text-slate-500">{{ $r->updated_at->diffForHumans() }}</span>
                        <x-badge :enum="$r->status" />
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
