{{--
    Form pengajuan perpanjangan (Admin TI & PIC Branch).
    Variabel: $license
--}}
@php
    $suggestStart = $license->end_date ? $license->end_date->copy()->addDay() : now();
    $suggestEnd = $suggestStart->copy()->addYear()->subDay();
@endphp

@if ($license->openRenewal)
    <div class="card flex items-start gap-3 border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
        <x-icon name="exclamation-triangle" class="h-5 w-5 shrink-0" />
        <div>
            <p class="font-semibold">Lisensi ini sudah memiliki pengajuan yang sedang berjalan.</p>
            <p class="mt-1">{{ $license->openRenewal->request_number }} — {{ $license->openRenewal->status->label() }}.
                <a href="{{ role_route('renewals.show', $license->openRenewal) }}" class="font-medium underline">Lihat pengajuan</a></p>
        </div>
    </div>
@else
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ role_route('renewals.store', $license) }}" enctype="multipart/form-data" class="card lg:col-span-2">
            @csrf
            <div class="card-header"><h2 class="card-title">Data Perpanjangan</h2></div>
            <div class="grid gap-5 p-5 sm:grid-cols-2">
                <x-form.input type="date" name="new_start_date" label="Usulan tanggal mulai" :value="$suggestStart" required />
                <x-form.input type="date" name="new_end_date" label="Usulan tanggal berakhir" :value="$suggestEnd" required hint="Default 1 tahun setelah tanggal mulai" />
                <div class="sm:col-span-2">
                    <x-form.input name="new_name" label="Paket lisensi" :value="$license->name" hint="Ubah jika paket/level lisensi berganti" />
                </div>
                <x-form.money name="estimated_cost" label="Estimasi biaya" />
                <x-form.select name="attachment_category" label="Jenis lampiran"
                               :options="['other' => 'Penawaran harga / lainnya', 'license_certificate' => 'Bukti lisensi saat ini', 'device_photo' => 'Foto stiker SN']" />
                <div class="sm:col-span-2">
                    <label class="form-label">Lampiran pendukung</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                    <p class="form-hint">PDF/JPG/PNG maks. 5 MB</p>
                </div>
                <div class="sm:col-span-2">
                    <x-form.textarea name="reason" label="Alasan / catatan" rows="4" required placeholder="Contoh: Lisensi berakhir 4 Feb 2027, perangkat masih digunakan sebagai firewall utama branch." />
                </div>
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><x-icon name="paper-airplane" class="h-4 w-4" /> Ajukan ke Manajemen</button>
            </div>
        </form>

        <div class="space-y-6">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Lisensi yang diperpanjang</h2></div>
                <div class="card-body space-y-3 text-sm">
                    @include('shared.licenses._asset-cell', ['license' => $license])
                    <dl class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Paket</dt><dd class="text-right">{{ $license->name }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Lokasi</dt><dd>{{ $license->branch->name }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Periode</dt><dd>{{ tgl($license->start_date) }} – {{ tgl($license->end_date) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">Status</dt><dd><x-badge :enum="$license->expiry_status" /></dd></div>
                    </dl>
                </div>
            </div>
            <div class="card p-5 text-sm text-slate-600 dark:text-slate-300">
                <p class="mb-3 font-semibold text-slate-900 dark:text-white">Alur setelah diajukan</p>
                <ol class="space-y-2">
                    <li class="flex gap-2"><span class="font-semibold text-brand-600">1.</span> Manajemen meninjau & menyetujui/menolak.</li>
                    <li class="flex gap-2"><span class="font-semibold text-brand-600">2.</span> Admin TI memproses pengadaan.</li>
                    <li class="flex gap-2"><span class="font-semibold text-brand-600">3.</span> Admin TI menginput periode & license key baru; periode lama tersimpan sebagai riwayat.</li>
                </ol>
            </div>
        </div>
    </div>
@endif
