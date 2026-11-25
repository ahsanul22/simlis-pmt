@extends('layouts.app')

@section('title', 'Input Lisensi Baru')

@section('content')
<x-page-header title="Selesaikan Perpanjangan" :back="route('admin-ti.renewals.show', $renewal)"
               :subtitle="$renewal->request_number.' · '.$license->branch->name.' · '.($license->device?->model->name ?? $license->softwareProduct?->name)" />

@if ($renewal->status !== \App\Enums\RenewalStatus::APPROVED)
    <div class="card flex items-center gap-3 p-5 text-sm">
        <x-icon name="information-circle" class="h-5 w-5 text-slate-400" />
        Pengajuan ini berstatus <x-badge :enum="$renewal->status" /> sehingga tidak dapat diselesaikan.
    </div>
@else
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin-ti.renewals.complete', $renewal) }}" enctype="multipart/form-data" class="card lg:col-span-2">
            @csrf
            @method('PUT')
            <div class="card-header"><h2 class="card-title">Data Lisensi Baru</h2></div>
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input name="new_name" label="Nama paket" :value="$renewal->new_name ?? $license->name" required />
                </div>
                <x-form.input type="date" name="new_start_date" label="Tanggal mulai" :value="$renewal->new_start_date" required />
                <x-form.input type="date" name="new_end_date" label="Tanggal berakhir" :value="$renewal->new_end_date" required />
                @if ($license->key_format === \App\Enums\LicenseKeyFormat::TEXT)
                    <div class="sm:col-span-2">
                        <x-form.textarea name="new_license_key" label="License key baru" rows="2" class="font-mono" hint="Disimpan terenkripsi. Kosongkan bila key tidak berubah." />
                    </div>
                @elseif ($license->key_format === \App\Enums\LicenseKeyFormat::IMAGE)
                    <div class="sm:col-span-2">
                        <label class="form-label">Foto license key baru</label>
                        <input type="file" name="key_images[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                        <p class="form-hint">Kosongkan bila key tidak berubah.</p>
                    </div>
                @endif
                <x-form.money name="actual_cost" label="Biaya aktual" :value="$renewal->actual_cost ?? $renewal->estimated_cost" />
                <x-form.input name="po_number" label="No. PO" :value="$renewal->po_number" />
                <div class="sm:col-span-2">
                    <label class="form-label">Bukti lisensi baru <span class="text-rose-500">*</span></label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" required
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                    <p class="form-hint">Sertifikat / email aktivasi / screenshot portal vendor</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
                <a href="{{ route('admin-ti.renewals.show', $renewal) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success"><x-icon name="check-circle" class="h-4 w-4" /> Simpan & Selesaikan</button>
            </div>
        </form>

        <div class="space-y-6">
            <div class="card p-5 text-sm">
                <p class="font-semibold text-slate-900 dark:text-white">Yang terjadi setelah disimpan</p>
                <ul class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                    <li class="flex gap-2"><x-icon name="check-circle" class="h-4 w-4 shrink-0 text-emerald-500" /> Periode lama ({{ tgl($license->start_date) }} – {{ tgl($license->end_date) }}) tersimpan sebagai riwayat.</li>
                    <li class="flex gap-2"><x-icon name="check-circle" class="h-4 w-4 shrink-0 text-emerald-500" /> Lisensi diperbarui dengan periode baru{{ $license->key_format === \App\Enums\LicenseKeyFormat::NONE ? '' : ' & key baru (bila diisi)' }}.</li>
                    <li class="flex gap-2"><x-icon name="check-circle" class="h-4 w-4 shrink-0 text-emerald-500" /> Pengaju & PIC branch mendapat notifikasi.</li>
                </ul>
            </div>
            <div class="card p-5 text-sm">
                <p class="text-slate-500">Disetujui oleh</p>
                <p class="font-medium">{{ $renewal->reviewer?->name ?? '-' }}</p>
                <p class="text-xs text-slate-500">{{ tgl_waktu($renewal->reviewed_at) }}</p>
                @if ($renewal->review_note)<p class="mt-2 rounded-lg bg-slate-50 p-2 text-xs dark:bg-slate-800">{{ $renewal->review_note }}</p>@endif
            </div>
        </div>
    </div>
@endif
@endsection
