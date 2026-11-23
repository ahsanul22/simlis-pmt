@extends('layouts.app')

@php $isEdit = $model->exists; @endphp

@section('title', $isEdit ? 'Ubah Model' : 'Tambah Model')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah Model Perangkat' : 'Tambah Model Perangkat'" :subtitle="$isEdit ? $model->name : null" :back="route('super-admin.device-models.index')" />

<form method="POST" action="{{ $isEdit ? route('super-admin.device-models.update', $model) : route('super-admin.device-models.store') }}" class="card max-w-2xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="grid gap-5 p-5 sm:grid-cols-2">
        <x-form.select name="vendor_id" label="Vendor" :options="$vendors->pluck('name', 'id')" :value="$model->vendor_id" placeholder="Pilih vendor" required />
        <x-form.select name="category" label="Kategori" :value="$model->category" required
                       :options="collect($categories)->mapWithKeys(fn ($c) => [$c->value => $c->label()])" />
        <div class="sm:col-span-2"><x-form.input name="name" label="Nama model" :value="$model->name" required placeholder="mis. PA-440" /></div>
        <div class="sm:col-span-2"><x-form.textarea name="description" label="Deskripsi" :value="$model->description" /></div>
        <div class="sm:col-span-2"><x-form.toggle name="is_active" label="Model aktif" :checked="$model->is_active" hint="Model nonaktif tidak muncul di form perangkat baru" /></div>
    </div>
    <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
        <a href="{{ route('super-admin.device-models.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
