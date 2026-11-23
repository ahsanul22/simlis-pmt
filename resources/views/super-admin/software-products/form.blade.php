@extends('layouts.app')

@php $isEdit = $product->exists; @endphp

@section('title', $isEdit ? 'Ubah Produk' : 'Tambah Produk')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah Produk Software' : 'Tambah Produk Software'" :subtitle="$isEdit ? $product->name : null" :back="route('super-admin.software-products.index')" />

<form method="POST" action="{{ $isEdit ? route('super-admin.software-products.update', $product) : route('super-admin.software-products.store') }}" class="card max-w-2xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="grid gap-5 p-5 sm:grid-cols-2">
        <x-form.select name="vendor_id" label="Vendor" :options="$vendors->pluck('name', 'id')" :value="$product->vendor_id" placeholder="Pilih vendor" required />
        <x-form.select name="category" label="Kategori" :value="$product->category" placeholder="Pilih kategori"
                       :options="array_combine($categories, $categories)" />
        <x-form.input name="name" label="Nama produk" :value="$product->name" required placeholder="mis. Microsoft 365" />
        <x-form.input name="edition" label="Edisi" :value="$product->edition" placeholder="mis. E3" />
        <div class="sm:col-span-2"><x-form.textarea name="description" label="Deskripsi" :value="$product->description" /></div>
        <div class="sm:col-span-2"><x-form.toggle name="is_active" label="Produk aktif" :checked="$product->is_active" /></div>
    </div>
    <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
        <a href="{{ route('super-admin.software-products.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
