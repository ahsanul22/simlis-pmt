@extends('layouts.app')

@section('title', $renewal->request_number)

@section('content')
@php $pending = $renewal->status === \App\Enums\RenewalStatus::SUBMITTED; @endphp

<x-page-header :title="'Pengajuan '.$renewal->request_number" :back="route('manajemen.approvals.index')"
               :subtitle="$license->branch->name.' · '.($license->device?->model->name ?? $license->softwareProduct?->name).' · diajukan '.($renewal->requester?->name ?? '-')">
    @if ($pending)
        <x-slot:actions>
            <button type="button" class="btn btn-secondary text-rose-600 dark:text-rose-400" @click="$dispatch('open-modal', 'reject')"><x-icon name="x-circle" class="h-4 w-4" /> Tolak</button>
            <button type="button" class="btn btn-success" @click="$dispatch('open-modal', 'approve')"><x-icon name="check-circle" class="h-4 w-4" /> Setujui</button>
        </x-slot:actions>
    @endif
</x-page-header>

@include('shared.renewals._detail')

@if ($pending)
    <x-modal name="approve" title="Setujui Pengajuan">
        <form id="form-approve" method="POST" action="{{ route('manajemen.approvals.approve', $renewal) }}" class="space-y-4">
            @csrf
            <p class="text-sm text-slate-600 dark:text-slate-300">Pengajuan akan diteruskan ke Admin TI untuk diproses pengadaannya.</p>
            <x-form.textarea name="review_note" id="approve_note" label="Catatan (opsional)" placeholder="Contoh: Disetujui, gunakan anggaran OPEX TI 2027." />
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'approve')">Batal</button>
            <button type="submit" form="form-approve" class="btn btn-success">Setujui</button>
        </x-slot:footer>
    </x-modal>

    <x-modal name="reject" title="Tolak Pengajuan">
        <form id="form-reject" method="POST" action="{{ route('manajemen.approvals.reject', $renewal) }}" class="space-y-4">
            @csrf
            <x-form.textarea name="review_note" id="reject_note" label="Alasan penolakan" required placeholder="Jelaskan alasan agar pengaju dapat memperbaiki pengajuan." />
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" @click="$dispatch('close-modal', 'reject')">Batal</button>
            <button type="submit" form="form-reject" class="btn btn-danger">Tolak Pengajuan</button>
        </x-slot:footer>
    </x-modal>
@endif
@endsection
