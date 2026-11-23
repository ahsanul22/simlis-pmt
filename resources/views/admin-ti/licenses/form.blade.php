@extends('layouts.app')

@php $isEdit = $license->exists; @endphp

@section('title', $isEdit ? 'Ubah Lisensi' : 'Tambah Lisensi')

@section('content')
<x-page-header :title="$isEdit ? 'Ubah Lisensi' : 'Tambah Lisensi'"
               :subtitle="$isEdit ? $license->code.' · '.$license->name : 'Daftarkan lisensi hardware atau software baru'"
               :back="$isEdit ? route('admin-ti.licenses.show', $license) : route('admin-ti.licenses.index')" />

<form method="POST" action="{{ $isEdit ? route('admin-ti.licenses.update', $license) : route('admin-ti.licenses.store') }}"
      enctype="multipart/form-data"
      x-data="{
          assetType: @js(old('asset_type', $license->asset_type?->value)),
          licenseType: @js(old('license_type', $license->license_type?->value)),
          keyFormat: @js(old('key_format', $license->key_format?->value ?? 'none')),
          vendor: @js((string) old('vendor_id', $license->vendor_id)),
          deviceId: @js((string) old('device_id', $license->device_id)),
          devices: @js($devices),
          software: @js($softwareProducts),
          get filteredDevices() { return this.vendor ? this.devices.filter(d => String(d.vendor_id) === this.vendor) : this.devices },
          get filteredSoftware() { return this.vendor ? this.software.filter(s => String(s.vendor_id) === this.vendor) : this.software },
      }"
      class="grid gap-6 lg:grid-cols-3">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="space-y-6 lg:col-span-2">
        {{-- Jenis --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">1. Jenis Lisensi</h2></div>
            <div class="card-body space-y-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($assetTypes as $type)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition"
                               :class="assetType === '{{ $type->value }}' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/5' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="asset_type" value="{{ $type->value }}" x-model="assetType" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="flex items-center gap-2 font-medium"><x-icon :name="$type->value === 'hardware' ? 'server-stack' : 'code-bracket'" class="h-4 w-4" /> {{ $type->label() }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $type->value === 'hardware' ? 'Subscription / warranty / support perangkat' : 'Lisensi aplikasi, OS, antivirus, dll.' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Tipe lisensi <span class="text-rose-500">*</span></label>
                        <select name="license_type" x-model="licenseType" class="form-control" required>
                            @foreach ($licenseTypes as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        @error('license_type')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Vendor <span class="text-rose-500">*</span></label>
                        <select name="vendor_id" x-model="vendor" class="form-control" required>
                            <option value="">Pilih vendor</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Aset --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">2. Aset & Lokasi</h2></div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2" x-show="assetType === 'hardware'">
                    <label class="form-label">Perangkat <span class="text-rose-500">*</span></label>
                    <select name="device_id" x-model="deviceId" class="form-control" :disabled="assetType !== 'hardware'">
                        <option value="">Pilih perangkat</option>
                        <template x-for="d in filteredDevices" :key="d.id">
                            <option :value="d.id" x-text="d.label" :selected="String(d.id) === deviceId"></option>
                        </template>
                    </select>
                    <p class="form-hint">Daftar perangkat menyesuaikan vendor. Belum ada? <a href="{{ route('admin-ti.devices.create') }}" class="text-brand-600 hover:underline">Tambah perangkat</a></p>
                    @error('device_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2" x-show="assetType === 'software'" x-cloak>
                    <label class="form-label">Produk software <span class="text-rose-500">*</span></label>
                    <select name="software_product_id" class="form-control" :disabled="assetType !== 'software'">
                        <option value="">Pilih produk</option>
                        <template x-for="s in filteredSoftware" :key="s.id">
                            <option :value="s.id" x-text="s.label" :selected="String(s.id) === @js((string) old('software_product_id', $license->software_product_id))"></option>
                        </template>
                    </select>
                    @error('software_product_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <x-form.select name="branch_id" label="Lokasi" :options="$branches->pluck('name', 'id')" :value="$license->branch_id"
                               placeholder="Pilih lokasi" required hint="Untuk hardware, otomatis mengikuti lokasi perangkat" />
                <div x-show="assetType === 'software'" x-cloak class="grid grid-cols-2 gap-3">
                    <x-form.input type="number" name="seats" label="Jumlah seat" :value="$license->seats" min="1" />
                    <x-form.input type="number" name="seats_used" label="Terpakai" :value="$license->seats_used" min="0" />
                </div>
            </div>
        </div>

        {{-- Detail --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">3. Detail & Masa Berlaku</h2></div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input name="name" label="Nama paket lisensi" :value="$license->name" required placeholder="Contoh: Threat Prevention + URL Filtering" />
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">License key</label>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach (\App\Enums\LicenseKeyFormat::cases() as $fmt)
                            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border-2 p-3 text-sm transition"
                                   :class="keyFormat === '{{ $fmt->value }}' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/5' : 'border-slate-200 dark:border-slate-700'">
                                <input type="radio" name="key_format" value="{{ $fmt->value }}" x-model="keyFormat" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                                <span>
                                    <span class="flex items-center gap-1.5 font-medium"><x-icon :name="$fmt->icon()" class="h-4 w-4" /> {{ $fmt->label() }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $fmt->description() }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('key_format')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2" x-show="keyFormat === 'text'" x-cloak>
                    <x-form.textarea name="license_key" label="Kode license key / authorization code" :value="$license->license_key" rows="2"
                                     hint="Disimpan terenkripsi. Hanya Admin TI yang bisa melihat." class="font-mono" />
                </div>
                <div class="sm:col-span-2" x-show="keyFormat === 'image'" x-cloak>
                    <label class="form-label">Foto license key</label>
                    <input type="file" name="key_images[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                    <p class="form-hint">
                        Foto stiker / kartu / screenshot yang memuat key (JPG, PNG, PDF — maks. 5 MB per file). Hanya Admin TI yang bisa melihat.
                        @if ($isEdit && ($n = $license->keyImages()->count())) Sudah ada {{ $n }} file; unggahan baru akan ditambahkan. @endif
                    </p>
                    @error('key_images.*')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <x-form.input type="date" name="start_date" label="Tanggal mulai" :value="$license->start_date" />
                <div x-show="licenseType !== 'perpetual'">
                    <x-form.input type="date" name="end_date" label="Tanggal berakhir" :value="$license->end_date" hint="Kosongkan hanya untuk lisensi perpetual" />
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Pengadaan</h2></div>
            <div class="card-body space-y-4">
                <x-form.input type="number" name="cost" label="Nilai (Rp)" :value="$license->cost" min="0" step="1000" />
                <x-form.input name="supplier" label="Supplier / reseller" :value="$license->supplier" />
                <x-form.input name="contract_number" label="No. kontrak" :value="$license->contract_number" />
                <x-form.input name="po_number" label="No. PO" :value="$license->po_number" />
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title">Lainnya</h2></div>
            <div class="card-body space-y-4">
                @unless ($isEdit)
                    <div>
                        <label class="form-label">Bukti lisensi</label>
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                        <p class="form-hint">Sertifikat / screenshot portal vendor</p>
                    </div>
                @endunless
                <x-form.textarea name="notes" label="Catatan" :value="$license->notes" />
                @if ($isEdit)
                    <x-form.toggle name="is_terminated" label="Hentikan lisensi" :checked="$license->is_terminated"
                                   hint="Tidak akan diperpanjang & tidak dipantau lagi" />
                @endif
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ $isEdit ? route('admin-ti.licenses.show', $license) : route('admin-ti.licenses.index') }}" class="btn btn-secondary flex-1">Batal</a>
            <button type="submit" class="btn btn-primary flex-1">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Lisensi' }}</button>
        </div>
    </div>
</form>
@endsection
