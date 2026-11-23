@extends('layouts.app')

@section('title', $license->code)

@section('content')
<x-page-header :title="$license->name" :back="route('manajemen.licenses.index')">
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        <span class="font-mono">{{ $license->code }}</span> · {{ $license->vendor->name }} · {{ $license->branch->name }}
    </p>
    @if ($license->openRenewal)
        <x-slot:actions>
            <a href="{{ route('manajemen.approvals.show', $license->openRenewal) }}" class="btn btn-primary"><x-icon name="arrow-path" class="h-4 w-4" /> Lihat Pengajuan</a>
        </x-slot:actions>
    @endif
</x-page-header>

@include('shared.licenses._detail')
@endsection
