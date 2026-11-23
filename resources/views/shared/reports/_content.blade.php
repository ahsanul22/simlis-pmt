{{--
    Isi halaman laporan (Admin TI & Manajemen).
    Variabel dari BuildsReport::reportData()
--}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card label="Total Lisensi" :value="$summary['total']" icon="key" />
    <x-stat-card label="Butuh Perhatian" :value="$summary['attention']" icon="exclamation-triangle" tone="orange" hint="Akan habis + kritis + expired" />
    <x-stat-card label="Perpanjangan Selesai" :value="$renewalCounts['completed']" icon="check-circle" tone="success" />
    <x-stat-card label="Estimasi Biaya 90 Hari" :value="rupiah($upcomingCost90)" icon="banknotes" tone="violet" hint="Dari nilai lisensi yang berakhir ≤ 90 hari" />
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Komposisi Status</h2></div>
        <div class="card-body"><div class="h-64"><canvas x-data="chart(@js($statusChart))"></canvas></div></div>
    </div>
    <div class="card lg:col-span-2">
        <div class="card-header"><h2 class="card-title">Status per Vendor</h2></div>
        <div class="card-body"><div class="h-64"><canvas x-data="chart(@js($vendorChart))"></canvas></div></div>
    </div>
    <div class="card lg:col-span-3">
        <div class="card-header">
            <h2 class="card-title">Jadwal Berakhir 12 Bulan ke Depan</h2>
            <span class="text-xs text-slate-500">Gunakan untuk perencanaan anggaran perpanjangan</span>
        </div>
        <div class="card-body"><div class="h-64"><canvas x-data="chart(@js($timelineChart))"></canvas></div></div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    {{-- Rekap per lokasi --}}
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="card-title">Rekap per Lokasi</h2></div>
        <div class="max-h-[28rem] overflow-auto">
            <table class="table">
                <thead class="sticky top-0"><tr><th>Lokasi</th><th class="text-center">Total</th><th class="text-center">Akan Habis</th><th class="text-center">Kritis</th><th class="text-center">Expired</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($byBranch as $row)
                        <tr>
                            <td class="font-medium">{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['total'] }}</td>
                            <td class="text-center {{ $row['warning'] ? 'font-semibold text-amber-600' : 'text-slate-300 dark:text-slate-600' }}">{{ $row['warning'] }}</td>
                            <td class="text-center {{ $row['critical'] ? 'font-semibold text-orange-600' : 'text-slate-300 dark:text-slate-600' }}">{{ $row['critical'] }}</td>
                            <td class="text-center {{ $row['expired'] ? 'font-semibold text-rose-600' : 'text-slate-300 dark:text-slate-600' }}">{{ $row['expired'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daftar berakhir ≤ 90 hari --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="card-title">Berakhir / Expired dalam 90 Hari</h2>
            <span class="text-xs text-slate-500">{{ $expiringSoon->count() }} lisensi</span>
        </div>
        <div class="max-h-[28rem] overflow-auto">
            <table class="table">
                <thead class="sticky top-0"><tr><th>Aset</th><th>Lokasi</th><th>Berakhir</th><th>Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($expiringSoon as $l)
                        <tr>
                            <td><a href="{{ role_route('licenses.show', $l) }}" class="font-medium hover:text-brand-600">{{ $l->device?->model->name ?? $l->softwareProduct?->name }}</a>
                                <p class="text-xs text-slate-500">{{ $l->vendor->name }}</p></td>
                            <td>{{ $l->branch->name }}</td>
                            <td class="text-xs">{{ tgl($l->end_date) }}<p class="text-slate-400">{{ sisa_hari($l->days_remaining) }}</p></td>
                            <td><x-badge :enum="$l->expiry_status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="shield-check" title="Tidak ada lisensi yang berakhir dalam 90 hari" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
