@extends('layouts.app')

@section('title', 'Perpanjangan Saya')

@section('content')
<x-page-header title="Perpanjangan Saya" subtitle="Status pengajuan perpanjangan lisensi branch Anda">
    <x-slot:actions>
        <a href="{{ route('pic-branch.licenses.index', ['status' => 'warning']) }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Ajukan dari Daftar Lisensi</a>
    </x-slot:actions>
</x-page-header>

@include('shared.renewals._tabs', ['tabs' => [
    'active' => 'Sedang Berjalan',
    'completed' => 'Selesai',
    'closed' => 'Ditolak / Batal',
]])

@include('shared.renewals._table', ['showRoute' => 'renewals.show'])
@endsection
