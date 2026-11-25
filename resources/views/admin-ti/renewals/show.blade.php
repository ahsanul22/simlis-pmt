@extends('layouts.app')

@section('title', $renewal->request_number)

@section('content')
@php $s = $renewal->status; @endphp

<x-page-header :title="'Pengajuan '.$renewal->request_number" :back="route('admin-ti.renewals.index')"
               :subtitle="$license->branch->name.' · '.($license->device?->model->name ?? $license->softwareProduct?->name)">
    <x-slot:actions>
        @if ($s === \App\Enums\RenewalStatus::SUBMITTED)
            <form method="POST" action="{{ route('admin-ti.renewals.cancel', $renewal) }}">
                @csrf
                <button class="btn btn-secondary"><x-icon name="x-circle" class="h-4 w-4" /> Batalkan</button>
            </form>
        @endif
        @if ($s === \App\Enums\RenewalStatus::APPROVED)
            <a href="{{ route('admin-ti.renewals.complete-form', $renewal) }}" class="btn btn-primary"><x-icon name="check-circle" class="h-4 w-4" /> Input Lisensi Baru</a>
        @endif
    </x-slot:actions>
</x-page-header>

@include('shared.renewals._detail')
@endsection
