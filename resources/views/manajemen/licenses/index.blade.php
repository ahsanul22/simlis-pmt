@extends('layouts.app')

@section('title', 'Daftar Lisensi')

@section('content')
<x-page-header title="Daftar Lisensi" subtitle="Pantau status lisensi hardware & software seluruh lokasi">
    <x-slot:actions>
        <a href="{{ route('manajemen.reports.export', request()->query()) }}" class="btn btn-secondary"><x-icon name="arrow-down-tray" class="h-4 w-4" /> Export</a>
    </x-slot:actions>
</x-page-header>

@include('shared.licenses._filters')
@include('shared.licenses._table')
@endsection
