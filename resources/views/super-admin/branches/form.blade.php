@extends('layouts.app')

@php $isEdit = $branch->exists; @endphp

@section('title', $isEdit ? 'Ubah Lokasi' : 'Tambah Lokasi')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah Lokasi' : 'Tambah Lokasi'" :subtitle="$isEdit ? $branch->name : null" :back="route('super-admin.branches.index')" />

<form method="POST" action="{{ $isEdit ? route('super-admin.branches.update', $branch) : route('super-admin.branches.store') }}" class="card max-w-2xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="grid gap-5 p-5 sm:grid-cols-2">
        <x-form.input name="code" label="Kode" :value="$branch->code" required class="font-mono uppercase" hint="Singkat & unik, mis. BIM" />
        <x-form.select name="type" label="Tipe" :value="$branch->type" required
                       :options="collect($types)->mapWithKeys(fn ($t) => [$t->value => $t->label()])" />
        <div class="sm:col-span-2"><x-form.input name="name" label="Nama lokasi" :value="$branch->name" required /></div>
        <div class="sm:col-span-2"><x-form.input name="region" label="Wilayah / Regional" :value="$branch->region" /></div>
        <div class="sm:col-span-2"><x-form.textarea name="address" label="Alamat" :value="$branch->address" /></div>
        <div class="sm:col-span-2"><x-form.toggle name="is_active" label="Lokasi aktif" :checked="$branch->is_active" /></div>
    </div>
    <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
        <a href="{{ route('super-admin.branches.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
