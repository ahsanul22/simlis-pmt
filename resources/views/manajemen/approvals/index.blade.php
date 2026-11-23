@extends('layouts.app')

@section('title', 'Pengajuan Perpanjangan')

@section('content')
<x-page-header title="Pengajuan Perpanjangan" subtitle="Tinjau dan setujui perpanjangan lisensi sebelum diproses Admin TI" />

@include('shared.renewals._tabs', ['tabs' => [
    'pending' => 'Menunggu Persetujuan',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
]])

@include('shared.renewals._table', ['showRoute' => 'approvals.show'])
@endsection
