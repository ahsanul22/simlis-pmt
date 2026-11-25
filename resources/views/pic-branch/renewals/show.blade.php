@extends('layouts.app')

@section('title', $renewal->request_number)

@section('content')
<x-page-header :title="'Pengajuan '.$renewal->request_number" :back="route('pic-branch.renewals.index')"
               :subtitle="$license->device?->model->name ?? $license->softwareProduct?->name">
    <x-slot:actions>
        @if ($renewal->status === \App\Enums\RenewalStatus::SUBMITTED && $renewal->requested_by === auth()->id())
            <form method="POST" action="{{ route('pic-branch.renewals.cancel', $renewal) }}">
                @csrf
                <button class="btn btn-secondary"><x-icon name="x-circle" class="h-4 w-4" /> Batalkan Pengajuan</button>
            </form>
        @endif
        @if ($renewal->status === \App\Enums\RenewalStatus::REJECTED && ! $license->openRenewal)
            <a href="{{ route('pic-branch.renewals.create', $license) }}" class="btn btn-primary"><x-icon name="arrow-path" class="h-4 w-4" /> Ajukan Ulang</a>
        @endif
    </x-slot:actions>
</x-page-header>

@include('shared.renewals._detail')
@endsection
