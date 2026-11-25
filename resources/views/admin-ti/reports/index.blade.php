@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<x-page-header title="Laporan Lisensi" :subtitle="'Data per '.now()->translatedFormat('d F Y').' · file unduhan mengikuti filter di bawah'">
    <x-slot:actions>
        <a href="{{ route('admin-ti.reports.export', ['format' => 'xlsx'] + $filters) }}" class="btn btn-secondary"><x-icon name="arrow-down-tray" class="h-4 w-4" /> Unduh Excel</a>
        <a href="{{ route('admin-ti.reports.export', ['format' => 'pdf'] + $filters) }}" class="btn btn-secondary"><x-icon name="document-text" class="h-4 w-4" /> Unduh PDF</a>
    </x-slot:actions>
</x-page-header>

@include('shared.reports._content')
@endsection
