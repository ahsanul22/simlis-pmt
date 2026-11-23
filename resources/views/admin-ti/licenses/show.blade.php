@extends('layouts.app')

@section('title', $license->code)

@section('content')
<x-page-header :title="$license->name" :back="route('admin-ti.licenses.index')">
    <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <span class="font-mono">{{ $license->code }}</span> · {{ $license->vendor->name }} · {{ $license->branch->name }}
    </p>
    <x-slot:actions>
        <x-delete-button :action="route('admin-ti.licenses.destroy', $license)" :item="'lisensi '.$license->code" label="Hapus" />
        <a href="{{ route('admin-ti.licenses.edit', $license) }}" class="btn btn-secondary"><x-icon name="pencil" class="h-4 w-4" /> Ubah</a>
        @if ($license->openRenewal)
            <a href="{{ route('admin-ti.renewals.show', $license->openRenewal) }}" class="btn btn-primary"><x-icon name="arrow-path" class="h-4 w-4" /> Lihat Pengajuan</a>
        @elseif ($license->end_date)
            <a href="{{ route('admin-ti.renewals.create', $license) }}" class="btn btn-primary"><x-icon name="arrow-path" class="h-4 w-4" /> Perpanjang</a>
        @endif
    </x-slot:actions>
</x-page-header>

@include('shared.licenses._detail')
@endsection
