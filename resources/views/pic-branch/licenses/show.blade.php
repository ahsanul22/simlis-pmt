@extends('layouts.app')

@section('title', $license->code)

@section('content')
<x-page-header :title="$license->name" :back="route('pic-branch.licenses.index')">
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><span class="font-mono">{{ $license->code }}</span> · {{ $license->vendor->name }}</p>
    <x-slot:actions>
        @if ($license->openRenewal)
            <a href="{{ route('pic-branch.renewals.show', $license->openRenewal) }}" class="btn btn-secondary"><x-icon name="arrow-path" class="h-4 w-4" /> Lihat Pengajuan</a>
        @elseif ($license->end_date)
            <a href="{{ route('pic-branch.renewals.create', $license) }}" class="btn btn-primary"><x-icon name="paper-airplane" class="h-4 w-4" /> Ajukan Perpanjangan</a>
        @endif
    </x-slot:actions>
</x-page-header>

@include('shared.licenses._detail')
@endsection
