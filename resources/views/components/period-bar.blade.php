{{--
    Progress masa berlaku lisensi: seberapa jauh periode sudah berjalan.
    <x-period-bar :license="$license" />
--}}
@props(['license', 'compact' => false, 'bare' => false])

@php
    $status = $license->expiry_status;
    $days = $license->days_remaining;
    $percent = null;
    if ($license->start_date && $license->end_date) {
        $total = max(1, $license->start_date->diffInDays($license->end_date));
        $elapsed = $license->start_date->diffInDays(now()->startOfDay(), false);
        $percent = max(0, min(100, round($elapsed / $total * 100)));
    } elseif ($days !== null && $days < 0) {
        $percent = 100;
    }
@endphp

<div {{ $attributes->class(['min-w-[8rem]']) }}>
    @unless ($bare)
    <div class="flex items-center justify-between gap-2 text-xs">
        <span class="font-semibold" style="color: {{ $status->color() }}">{{ sisa_hari($days) }}</span>
        @unless ($compact)
            <span class="text-slate-400">{{ tgl($license->end_date) }}</span>
        @endunless
    </div>
    @endunless
    @if ($percent !== null)
        <div class="{{ $bare ? '' : 'mt-1.5' }} h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div class="h-full rounded-full" style="width: {{ $percent }}%; background: {{ $status->color() }}"></div>
        </div>
    @endif
</div>
