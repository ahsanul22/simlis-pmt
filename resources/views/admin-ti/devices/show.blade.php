@extends('layouts.app')

@section('title', $device->serial_number)

@section('content')
<x-page-header :title="$device->model->name.' — '.$device->branch->name" :back="route('admin-ti.devices.index')">
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $device->model->vendor->name }} · <span class="font-mono">SN {{ $device->serial_number }}</span></p>
    <x-slot:actions>
        <x-delete-button :action="route('admin-ti.devices.destroy', $device)" :item="'perangkat '.$device->serial_number" label="Hapus" />
        <a href="{{ route('admin-ti.devices.edit', $device) }}" class="btn btn-secondary"><x-icon name="pencil" class="h-4 w-4" /> Ubah</a>
        <a href="{{ route('admin-ti.licenses.create', ['device' => $device->id]) }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Lisensi</a>
    </x-slot:actions>
</x-page-header>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title">Lisensi pada perangkat ini</h2>
                <span class="text-xs text-slate-500">{{ $device->licenses->count() }} lisensi</span>
            </div>
            @forelse ($device->licenses as $license)
                <a href="{{ route('admin-ti.licenses.show', $license) }}" class="flex flex-wrap items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/40">
                    <span class="h-10 w-1 rounded-full" style="background: {{ $license->expiry_status->color() }}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium">{{ $license->name }}</p>
                        <p class="text-xs text-slate-500"><span class="font-mono">{{ $license->code }}</span> · {{ $license->license_type->label() }}</p>
                    </div>
                    <div class="w-40"><x-period-bar :license="$license" /></div>
                    <x-badge :enum="$license->expiry_status" />
                </a>
            @empty
                <x-empty-state icon="key" title="Perangkat ini belum memiliki lisensi">
                    <a href="{{ route('admin-ti.licenses.create', ['device' => $device->id]) }}" class="font-medium text-brand-600 hover:underline">Tambahkan lisensi</a>
                </x-empty-state>
            @endforelse
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Foto & Dokumen Perangkat</h2>
                <button type="button" class="btn btn-ghost btn-sm" @click="$dispatch('open-modal', 'upload-attachment')"><x-icon name="arrow-up-tray" class="h-4 w-4" /> Unggah</button>
            </div>
            @include('shared.attachments._list', ['attachments' => $device->attachments])
        </div>
    </div>

    <div class="card h-fit">
        <div class="card-header"><h2 class="card-title">Informasi Perangkat</h2></div>
        <dl class="space-y-3 p-5 text-sm">
            @foreach ([
                'Vendor' => $device->model->vendor->name,
                'Model' => $device->model->name,
                'Kategori' => $device->model->category->label(),
                'Lokasi' => $device->branch->name,
                'Hostname' => $device->hostname ?: '-',
                'IP Address' => $device->ip_address ?: '-',
                'Tanggal instalasi' => tgl($device->installed_at),
            ] as $label => $value)
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right font-medium">{{ $value }}</dd></div>
            @endforeach
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-badge :enum="$device->status" /></dd></div>
            @if ($device->notes)
                <div class="border-t border-slate-100 pt-3 dark:border-slate-800"><dt class="text-slate-500">Catatan</dt><dd class="mt-1 whitespace-pre-line">{{ $device->notes }}</dd></div>
            @endif
        </dl>
    </div>
</div>

@include('shared.attachments._upload-modal')
@endsection
