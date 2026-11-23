@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<x-page-header title="Laporan Lisensi" :subtitle="'Data per '.now()->translatedFormat('d F Y')">
    <x-slot:actions>
        <a href="{{ route('admin-ti.reports.export', ['format' => 'xlsx']) }}" class="btn btn-secondary"><x-icon name="arrow-down-tray" class="h-4 w-4" /> Excel</a>
        <a href="{{ route('admin-ti.reports.export', ['format' => 'pdf']) }}" class="btn btn-secondary"><x-icon name="document-text" class="h-4 w-4" /> PDF</a>
    </x-slot:actions>
</x-page-header>

@include('shared.reports._content')
@endsection
