{{--
    Isi detail pengajuan perpanjangan: perbandingan periode, info, dan alur status.
    Variabel: $renewal, $license
--}}
@php
    $s = $renewal->status;
    $steps = [
        ['Diajukan', $renewal->submitted_at ?? $renewal->created_at, $renewal->requester?->name, true],
        [$s === \App\Enums\RenewalStatus::REJECTED ? 'Ditolak' : 'Disetujui', $renewal->reviewed_at, $renewal->reviewer?->name, (bool) $renewal->reviewed_at],
        ['Lisensi baru diinput', $renewal->completed_at, $renewal->completer?->name, $s === \App\Enums\RenewalStatus::COMPLETED],
    ];
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        {{-- Perbandingan periode --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">Perbandingan Periode</h2></div>
            <div class="grid gap-4 p-5 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periode lama</p>
                    <p class="mt-2 text-sm font-medium">{{ $renewal->old_name ?? '-' }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ tgl($renewal->old_start_date) }} – {{ tgl($renewal->old_end_date) }}</p>
                </div>
                <x-icon name="arrow-right" class="mx-auto hidden h-5 w-5 text-slate-400 sm:block" />
                <div class="rounded-xl border-2 border-brand-200 bg-brand-50/50 p-4 dark:border-brand-500/30 dark:bg-brand-500/5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Periode baru {{ $s === \App\Enums\RenewalStatus::COMPLETED ? '' : '(usulan)' }}</p>
                    <p class="mt-2 text-sm font-medium">{{ $renewal->new_name ?? $license->name }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ tgl($renewal->new_start_date) }} – {{ tgl($renewal->new_end_date) }}</p>
                </div>
            </div>
        </div>

        {{-- Info pengajuan --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">Detail Pengajuan</h2></div>
            <dl class="grid gap-x-6 gap-y-4 p-5 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Estimasi biaya</dt><dd class="mt-0.5 font-medium">{{ rupiah($renewal->estimated_cost) }}</dd></div>
                <div><dt class="text-slate-500">Biaya aktual</dt><dd class="mt-0.5 font-medium">{{ rupiah($renewal->actual_cost) }}</dd></div>
                <div><dt class="text-slate-500">No. PO</dt><dd class="mt-0.5 font-medium">{{ $renewal->po_number ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Status lisensi saat ini</dt><dd class="mt-0.5"><x-badge :enum="$license->expiry_status" /> <span class="ml-1 text-xs text-slate-500">{{ sisa_hari($license->days_remaining) }}</span></dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Alasan / catatan pengaju</dt><dd class="mt-0.5 whitespace-pre-line">{{ $renewal->reason ?: '-' }}</dd></div>
                @if ($renewal->review_note)
                    <div class="sm:col-span-2 rounded-lg p-3 {{ $s === \App\Enums\RenewalStatus::REJECTED ? 'bg-rose-50 dark:bg-rose-500/10' : 'bg-slate-50 dark:bg-slate-800/50' }}">
                        <dt class="text-slate-500">Catatan reviewer</dt>
                        <dd class="mt-0.5 whitespace-pre-line">{{ $renewal->review_note }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title">Lampiran</h2></div>
            @include('shared.attachments._list', ['attachments' => $renewal->attachments])
        </div>
    </div>

    <div class="space-y-6">
        {{-- Alur status --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Alur Proses</h2>
                <x-badge :enum="$s" />
            </div>
            <ol class="space-y-5 p-5">
                @foreach ($steps as $i => [$label, $at, $by, $done])
                    @php $isReject = $i === 1 && $s === \App\Enums\RenewalStatus::REJECTED; @endphp
                    <li class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold
                            {{ $done ? ($isReject ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white') : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                            @if ($done) <x-icon :name="$isReject ? 'x-mark' : 'check-circle'" class="h-4 w-4" /> @else {{ $i + 1 }} @endif
                        </span>
                        <div class="text-sm">
                            <p class="font-medium {{ $done ? '' : 'text-slate-400' }}">{{ $label }}</p>
                            @if ($done)
                                <p class="text-xs text-slate-500">{{ tgl_waktu($at) }} · {{ $by ?? '-' }}</p>
                            @else
                                <p class="text-xs text-slate-400">Menunggu</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Ringkasan lisensi --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">Lisensi</h2></div>
            <div class="card-body space-y-3 text-sm">
                @include('shared.licenses._asset-cell', ['license' => $license])
                <dl class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Kode</dt><dd class="font-mono">{{ $license->code }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Vendor</dt><dd>{{ $license->vendor->name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Lokasi</dt><dd>{{ $license->branch->name }}</dd></div>
                </dl>
                <a href="{{ role_route('licenses.show', $license) }}" class="btn btn-secondary btn-sm w-full">Lihat lisensi</a>
            </div>
        </div>
    </div>
</div>
