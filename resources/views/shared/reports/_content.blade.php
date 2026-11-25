{{--
    Isi halaman laporan (Admin TI & Manajemen).
    Variabel dari BuildsReport::reportData(): $report (App\Services\LicenseReport), $filters, $vendors, $branches, $assetTypes,
    $summary, $statusChart, $vendorChart, $timelineChart, $byBranch, $upcomingCost90, $renewalCounts
--}}
@php
    $rab = $report->summary();
    $months = $report->byMonth();
    $rabTotal = $report->totalCost();
@endphp

{{-- ===================== Filter laporan (ikut ke Excel/PDF) ===================== --}}
<form method="GET" class="card mb-6 p-4">
    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <select name="vendor" class="form-control" aria-label="Vendor">
            <option value="">Semua vendor</option>
            @foreach ($vendors as $v)
                <option value="{{ $v->id }}" @selected(($filters['vendor'] ?? '') == $v->id)>{{ $v->name }}</option>
            @endforeach
        </select>
        @if ($branches->isNotEmpty())
            <select name="branch" class="form-control" aria-label="Lokasi">
                <option value="">Semua lokasi</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected(($filters['branch'] ?? '') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        @endif
        <select name="asset_type" class="form-control" aria-label="Jenis aset">
            <option value="">Hardware & Software</option>
            @foreach ($assetTypes as $t)
                <option value="{{ $t->value }}" @selected(($filters['asset_type'] ?? '') === $t->value)>{{ $t->label() }}</option>
            @endforeach
        </select>
    </div>
    @include('shared._expiry-period-fields')
</form>

{{-- ===================== Rencana perpanjangan (RAB) ===================== --}}
<div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Rencana Perpanjangan · {{ $report->period->label() }}</h2>
    <span class="text-xs text-slate-500 dark:text-slate-400">Lisensi yang berakhir pada periode terpilih — dasar penyusunan RAB</span>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <x-stat-card label="Jumlah Perlu Diperpanjang" :value="$report->totalQty()" icon="key" hint="Total qty (unit / user)" />
    <x-stat-card label="Jenis Item" :value="$rab->count()" icon="squares-2x2" tone="violet" hint="Kombinasi perangkat & paket lisensi" />
    <x-stat-card label="Estimasi Biaya" :value="$rabTotal !== null ? rupiah($rabTotal) : '-'" icon="banknotes" tone="success"
                 :hint="$rabTotal !== null ? 'Dari item yang sudah ada nilai lisensinya' : 'Nilai lisensi belum diisi — lengkapi harga di Excel'" />
</div>

<div class="card mt-4 overflow-hidden">
    <div class="card-header">
        <h2 class="card-title">Rekap per Item</h2>
        <span class="text-xs text-slate-500">Qty = jumlah perangkat/user dengan paket yang sama</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>Item</th><th>Paket Lisensi</th><th class="text-center">Qty</th><th>Berakhir</th><th>Lokasi</th><th class="text-right">Harga Satuan</th><th class="text-right">Jumlah</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($rab as $s)
                    <tr>
                        <td><p class="font-medium">{{ $s['item'] }}</p><p class="text-xs text-slate-500">{{ $s['vendor'] }}</p></td>
                        <td>{{ $s['package'] }}<p class="text-xs text-slate-500">{{ $s['license_type'] }}</p></td>
                        <td class="text-center"><span class="text-base font-semibold">{{ $s['qty'] }}</span> <span class="text-xs text-slate-500">{{ $s['unit'] }}</span></td>
                        <td class="whitespace-nowrap text-xs">{{ tgl($s['first_end']) }}
                            @if (! $s['first_end']->isSameDay($s['last_end']))<p class="text-slate-400">s/d {{ tgl($s['last_end']) }}</p>@endif</td>
                        <td class="max-w-xs text-xs text-slate-600 dark:text-slate-300">{{ implode(', ', $s['branches']) }}</td>
                        <td class="whitespace-nowrap text-right">{{ $s['unit_price'] !== null ? rupiah($s['unit_price']) : '-' }}</td>
                        <td class="whitespace-nowrap text-right font-medium">{{ $s['total'] !== null ? rupiah($s['total']) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="shield-check" title="Tidak ada lisensi yang berakhir pada periode ini">Ubah periode atau filter di atas.</x-empty-state></td></tr>
                @endforelse
            </tbody>
            @if ($rab->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-50 font-semibold dark:bg-slate-800/60">
                        <td colspan="2">Total</td>
                        <td class="text-center">{{ $report->totalQty() }}</td>
                        <td colspan="3"></td>
                        <td class="whitespace-nowrap text-right">{{ $rabTotal !== null ? rupiah($rabTotal) : '-' }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    {{-- Jadwal per bulan (periode terpilih) --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="card-title">Jadwal Berakhir per Bulan</h2>
            <span class="text-xs text-slate-500">{{ $report->period->label() }}</span>
        </div>
        <div class="max-h-[28rem] overflow-auto">
            <table class="table">
                <thead class="sticky top-0"><tr><th>Bulan</th><th>Item</th><th class="text-center">Qty</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($months as $m)
                        <tr>
                            <td class="whitespace-nowrap font-medium">{{ ucfirst($m['month']->translatedFormat('F Y')) }}</td>
                            <td>{{ $m['item'] }} <span class="text-slate-400">·</span> <span class="text-xs text-slate-500">{{ $m['package'] }}</span></td>
                            <td class="text-center">{{ $m['qty'] }} <span class="text-xs text-slate-500">{{ $m['unit'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="calendar" title="Tidak ada data pada periode ini" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Rekap per lokasi (seluruh lisensi) --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="card-title">Rekap per Lokasi</h2>
            <span class="text-xs text-slate-500">Seluruh lisensi, status hari ini</span>
        </div>
        <div class="max-h-[28rem] overflow-auto">
            <table class="table">
                <thead class="sticky top-0"><tr><th>Lokasi</th><th class="text-center">Total</th><th class="text-center">Akan Habis</th><th class="text-center">Kritis</th><th class="text-center">Expired</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($byBranch as $row)
                        <tr>
                            <td class="font-medium">{{ $row['name'] }}</td>
                            <td class="text-center">{{ $row['total'] }}</td>
                            <td class="text-center {{ $row['warning'] ? 'font-semibold text-amber-600 dark:text-amber-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $row['warning'] }}</td>
                            <td class="text-center {{ $row['critical'] ? 'font-semibold text-orange-600 dark:text-orange-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $row['critical'] }}</td>
                            <td class="text-center {{ $row['expired'] ? 'font-semibold text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-slate-600' }}">{{ $row['expired'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===================== Kondisi seluruh lisensi ===================== --}}
<div class="mb-2 mt-10 flex flex-wrap items-baseline justify-between gap-2">
    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Kondisi Seluruh Lisensi</h2>
    <span class="text-xs text-slate-500 dark:text-slate-400">Status per {{ tgl(now()) }}, tidak terpengaruh filter periode</span>
</div>

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
