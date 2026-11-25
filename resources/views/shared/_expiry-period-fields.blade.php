{{--
    Field filter "masa berakhir" (dari bulan/tahun s/d bulan/tahun) + tombol cepat.
    Dipakai di dalam <form method="GET">. Variabel: $filters (array query saat ini).
    Logika rentang: App\Support\ExpiryPeriod.
--}}
@php
    $periodMonths = \App\Support\ExpiryPeriod::monthOptions();
    $periodYears = \App\Support\ExpiryPeriod::yearOptions();
    $periodActive = \App\Support\ExpiryPeriod::fromArray($filters);
    $periodOthers = collect($filters)->except(\App\Support\ExpiryPeriod::KEYS)->filter()->except('page')->all();
@endphp

<div class="flex flex-col gap-3 lg:flex-row lg:items-end">
    <div class="grid flex-1 gap-3 sm:grid-cols-2">
        <fieldset>
            <legend class="form-label">Berakhir dari</legend>
            <div class="grid grid-cols-2 gap-2">
                <select name="from_month" class="form-control" aria-label="Bulan awal">
                    <option value="">Semua bulan</option>
                    @foreach ($periodMonths as $m => $name)
                        <option value="{{ $m }}" @selected(($filters['from_month'] ?? '') == $m)>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="from_year" class="form-control" aria-label="Tahun awal">
                    <option value="">Tahun</option>
                    @foreach ($periodYears as $y)
                        <option value="{{ $y }}" @selected(($filters['from_year'] ?? '') == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </fieldset>
        <fieldset>
            <legend class="form-label">Sampai</legend>
            <div class="grid grid-cols-2 gap-2">
                <select name="to_month" class="form-control" aria-label="Bulan akhir">
                    <option value="">Semua bulan</option>
                    @foreach ($periodMonths as $m => $name)
                        <option value="{{ $m }}" @selected(($filters['to_month'] ?? '') == $m)>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="to_year" class="form-control" aria-label="Tahun akhir">
                    <option value="">Tahun</option>
                    @foreach ($periodYears as $y)
                        <option value="{{ $y }}" @selected(($filters['to_year'] ?? '') == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </fieldset>
    </div>
    <button type="submit" class="btn btn-primary"><x-icon name="funnel" class="h-4 w-4" /> Terapkan</button>
</div>

<div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
    <span class="text-slate-500 dark:text-slate-400">Cepat:</span>
    @foreach (\App\Support\ExpiryPeriod::presets() as $presetLabel => $preset)
        <a href="{{ request()->url().'?'.http_build_query($periodOthers + $preset) }}"
           class="rounded-full px-2.5 py-1 font-medium ring-1 ring-inset {{ $periodActive->label() === \App\Support\ExpiryPeriod::fromArray($preset)->label() ? 'bg-brand-50 text-brand-700 ring-brand-600/30 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/30' : 'text-slate-600 ring-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
            {{ $presetLabel }}
        </a>
    @endforeach
    @if ($periodActive->isSet())
        <span class="ml-auto text-slate-500 dark:text-slate-400">Periode: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $periodActive->label() }}</span></span>
        <a href="{{ request()->url().'?'.http_build_query($periodOthers) }}" class="font-medium text-slate-500 hover:text-brand-600 dark:text-slate-400">Hapus periode</a>
    @endif
</div>
