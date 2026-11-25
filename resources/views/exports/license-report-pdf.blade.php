{{-- PDF laporan lisensi (dompdf, A4 landscape). Variabel: $report (App\Services\LicenseReport), $printedBy --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Lisensi</title>
    <style>
        @page { margin: 28px 30px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        h1 { font-size: 15px; margin: 0 0 4px; }
        h2 { font-size: 11px; margin: 18px 0 6px; color: #1e3a5f; }
        .meta td { padding: 1px 8px 1px 0; font-size: 9px; }
        .meta td:first-child { font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #1e3a5f; color: #fff; padding: 5px 4px; font-size: 8.5px; text-align: center; }
        table.data td { border: 1px solid #cbd5e1; padding: 4px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        table.data tr.total td { background: #e2e8f0; font-weight: bold; }
        .c { text-align: center; } .r { text-align: right; } .muted { color: #64748b; }
        .note { margin-top: 6px; font-style: italic; color: #64748b; font-size: 8px; }
        .page-break { page-break-before: always; }
        .footer { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="footer">{{ config('app.name') }} · Laporan Lisensi · dicetak {{ tgl_waktu(now()) }} oleh {{ $printedBy }}</div>

    <h1>Laporan Rencana Perpanjangan Lisensi TI</h1>
    <table class="meta">
        @foreach ($report->filterLabels() as $label => $value)
            <tr><td>{{ $label }}</td><td>: {{ $value }}</td></tr>
        @endforeach
        <tr><td>Jumlah</td><td>: {{ $report->totalQty() }} ({{ $report->licenses()->count() }} lisensi)</td></tr>
        @if (($total = $report->totalCost()) !== null)
            <tr><td>Estimasi biaya</td><td>: {{ rupiah($total) }} <span class="muted">(dari item yang sudah ada harganya)</span></td></tr>
        @endif
    </table>

    @php $summary = $report->summary(); @endphp
    <h2>1. Rekap Kebutuhan Perpanjangan (RAB)</h2>
    <table class="data">
        <thead>
            <tr><th>No</th><th>Vendor</th><th>Perangkat / Produk</th><th>Jenis</th><th>Paket Lisensi</th><th>Qty</th>
                <th>Berakhir</th><th>Lokasi</th><th>Harga Satuan</th><th>Jumlah Harga</th></tr>
        </thead>
        <tbody>
            @forelse ($summary as $i => $s)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $s['vendor'] }}</td>
                    <td>{{ $s['item'] }}</td>
                    <td>{{ $s['license_type'] }}</td>
                    <td>{{ $s['package'] }}</td>
                    <td class="c">{{ $s['qty'] }} {{ $s['unit'] }}</td>
                    <td class="c">{{ tgl($s['first_end']) }}@if (! $s['first_end']->isSameDay($s['last_end']))<br>s/d {{ tgl($s['last_end']) }}@endif</td>
                    <td>{{ implode(', ', $s['branches']) }}</td>
                    <td class="r">{{ $s['unit_price'] !== null ? rupiah($s['unit_price']) : '-' }}</td>
                    <td class="r">{{ $s['total'] !== null ? rupiah($s['total']) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="c muted">Tidak ada lisensi yang berakhir pada periode ini.</td></tr>
            @endforelse
            @if ($summary->isNotEmpty())
                <tr class="total">
                    <td colspan="5">TOTAL</td>
                    <td class="c">{{ $report->totalQty() }}</td>
                    <td colspan="3"></td>
                    <td class="r">{{ $total !== null ? rupiah($total) : '-' }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <p class="note">Harga satuan diambil dari nilai lisensi yang tercatat di sistem; "-" berarti belum ada nilai. Untuk mengisi harga, gunakan file Excel.</p>

    @php $months = $report->byMonth(); @endphp
    @if ($months->isNotEmpty())
        <h2>2. Jadwal Berakhir per Bulan</h2>
        <table class="data">
            <thead><tr><th>Bulan</th><th>Vendor</th><th>Perangkat / Produk</th><th>Paket Lisensi</th><th>Qty</th><th>Lokasi</th></tr></thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ ucfirst($m['month']->translatedFormat('F Y')) }}</td>
                        <td>{{ $m['vendor'] }}</td>
                        <td>{{ $m['item'] }}</td>
                        <td>{{ $m['package'] }}</td>
                        <td class="c">{{ $m['qty'] }} {{ $m['unit'] }}</td>
                        <td>{{ implode(', ', $m['branches']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="page-break"></div>
        <h2>3. Rincian Lisensi</h2>
        <table class="data">
            <thead><tr><th>No</th><th>Kode</th><th>Vendor</th><th>Perangkat / Produk</th><th>Serial Number</th><th>Lokasi</th>
                <th>Paket Lisensi</th><th>Mulai</th><th>Berakhir</th><th>Sisa</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($report->details() as $i => $d)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $d['code'] }}</td>
                        <td>{{ $d['vendor'] }}</td>
                        <td>{{ $d['item'] }}</td>
                        <td>{{ $d['serial_number'] ?? '-' }}</td>
                        <td>{{ $d['branch'] }}</td>
                        <td>{{ $d['package'] }}</td>
                        <td class="c">{{ $d['start_date'] ? tgl($d['start_date']) : '-' }}</td>
                        <td class="c">{{ tgl($d['end_date']) }}</td>
                        <td class="c">{{ $d['days_remaining'] }} hr</td>
                        <td>{{ $d['status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
