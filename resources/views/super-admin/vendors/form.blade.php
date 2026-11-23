@extends('layouts.app')

@php $isEdit = $vendor->exists; @endphp

@section('title', $isEdit ? 'Ubah Vendor' : 'Tambah Vendor')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah Vendor' : 'Tambah Vendor'" :subtitle="$isEdit ? $vendor->name : null" :back="route('super-admin.vendors.index')" />

<form method="POST" action="{{ $isEdit ? route('super-admin.vendors.update', $vendor) : route('super-admin.vendors.store') }}" class="card max-w-2xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="grid gap-5 p-5 sm:grid-cols-2">
        <x-form.input name="code" label="Kode" :value="$vendor->code" required class="font-mono uppercase" />
        <x-form.input name="name" label="Nama vendor" :value="$vendor->name" required />
        <div class="sm:col-span-2"><x-form.input type="url" name="website" label="Website" :value="$vendor->website" placeholder="https://" /></div>
        <p class="text-sm font-semibold text-slate-900 dark:text-white sm:col-span-2">Kontak Support</p>
        <x-form.input name="support_contact_name" label="Nama kontak" :value="$vendor->support_contact_name" />
        <x-form.input name="support_phone" label="Telepon" :value="$vendor->support_phone" />
        <div class="sm:col-span-2"><x-form.input type="email" name="support_email" label="Email support" :value="$vendor->support_email" /></div>
        <div class="sm:col-span-2"><x-form.textarea name="notes" label="Catatan" :value="$vendor->notes" /></div>
        <div class="sm:col-span-2"><x-form.toggle name="is_active" label="Vendor aktif" :checked="$vendor->is_active" /></div>
    </div>
    <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
        <a href="{{ route('super-admin.vendors.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
