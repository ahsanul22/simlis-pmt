@extends('layouts.app')

@php $isEdit = $device->exists; @endphp

@section('title', $isEdit ? 'Ubah Perangkat' : 'Tambah Perangkat')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah Perangkat' : 'Tambah Perangkat'"
               :subtitle="$isEdit ? 'SN '.$device->serial_number : 'Daftarkan unit perangkat baru ke inventaris'"
               :back="$isEdit ? route('admin-ti.devices.show', $device) : route('admin-ti.devices.index')" />

<form method="POST" action="{{ $isEdit ? route('admin-ti.devices.update', $device) : route('admin-ti.devices.store') }}" class="card max-w-3xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="grid gap-5 p-5 sm:grid-cols-2">
        <div>
            <label for="device_model_id" class="form-label">Model perangkat <span class="text-rose-500">*</span></label>
            <select id="device_model_id" name="device_model_id" class="form-control" required>
                <option value="">Pilih model</option>
                @foreach ($modelsByVendor as $vendor => $models)
                    <optgroup label="{{ $vendor }}">
                        @foreach ($models as $m)
                            <option value="{{ $m->id }}" @selected(old('device_model_id', $device->device_model_id) == $m->id)>{{ $m->name }} ({{ $m->category->label() }})</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('device_model_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <x-form.select name="branch_id" label="Lokasi" :options="$branches->pluck('name', 'id')" :value="$device->branch_id" placeholder="Pilih lokasi" required />
        <x-form.input name="serial_number" label="Serial Number" :value="$device->serial_number" required class="font-mono uppercase" />
        <x-form.select name="status" label="Status" :value="$device->status"
                       :options="collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])" required />
        <x-form.input name="hostname" label="Hostname" :value="$device->hostname" placeholder="mis. FW-BIMA-01" />
        <x-form.input name="ip_address" label="IP Address (management)" :value="$device->ip_address" placeholder="10.x.x.x" class="font-mono" />
        <x-form.input type="date" name="installed_at" label="Tanggal instalasi" :value="$device->installed_at" />
        <div class="sm:col-span-2">
            <x-form.textarea name="notes" label="Catatan" :value="$device->notes" />
        </div>
    </div>

    <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
        <a href="{{ $isEdit ? route('admin-ti.devices.show', $device) : route('admin-ti.devices.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Perangkat' }}</button>
    </div>
</form>
@endsection
