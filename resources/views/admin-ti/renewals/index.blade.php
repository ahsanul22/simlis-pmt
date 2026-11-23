@extends('layouts.app')

@section('title', 'Perpanjangan')

@section('content')
<x-page-header title="Perpanjangan Lisensi" subtitle="Pengajuan, proses, dan riwayat perpanjangan seluruh lokasi">
    <x-slot:actions>
        <a href="{{ route('admin-ti.licenses.index', ['status' => 'critical']) }}" class="btn btn-secondary"><x-icon name="exclamation-triangle" class="h-4 w-4" /> Lisensi Kritis</a>
    </x-slot:actions>
</x-page-header>

@include('shared.renewals._tabs', ['tabs' => [
    'to_complete' => 'Siap Diproses',
    'in_review' => 'Menunggu Persetujuan',
    'history' => 'Riwayat Selesai',
    'closed' => 'Ditolak / Batal',
]])

@include('shared.renewals._table', ['showRoute' => 'renewals.show'])
@endsection
