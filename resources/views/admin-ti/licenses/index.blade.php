@extends('layouts.app')

@section('title', 'Lisensi')

@section('content')
<x-page-header title="Lisensi" subtitle="Seluruh lisensi hardware & software aktif di semua lokasi">
    <x-slot:actions>
        <a href="{{ route('admin-ti.reports.export', request()->query()) }}" class="btn btn-secondary"><x-icon name="arrow-down-tray" class="h-4 w-4" /> Export</a>
        <a href="{{ route('admin-ti.licenses.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Lisensi</a>
    </x-slot:actions>
</x-page-header>

@include('shared.licenses._filters')
@include('shared.licenses._table')
@endsection
