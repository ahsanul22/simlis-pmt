{{--
    Tab status + form filter daftar lisensi.
    Variabel: $filters, $statusCounts, $totalCount, $vendors, $branches, $assetTypes, $licenseTypes
--}}
@php
    $current = $filters['status'] ?? null;
    $baseQuery = collect($filters)->except('status')->filter()->all();
@endphp

<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    <a href="{{ request()->url().'?'.http_build_query($baseQuery) }}"
       class="badge px-3 py-1.5 text-sm {{ ! $current ? 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900 dark:ring-white' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700' }}">
        Semua <span class="opacity-60">{{ $totalCount }}</span>
    </a>
    @foreach (\App\Enums\ExpiryStatus::cases() as $s)
        @continue($s === \App\Enums\ExpiryStatus::PERPETUAL && ! $statusCounts[$s->value])
        <a href="{{ request()->url().'?'.http_build_query($baseQuery + ['status' => $s->value]) }}"
           class="badge px-3 py-1.5 text-sm {{ $current === $s->value ? $s->badge().' ring-2' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700' }}">
            <span class="h-2 w-2 rounded-full" style="background: {{ $s->color() }}"></span>
            {{ $s->label() }} <span class="opacity-60">{{ $statusCounts[$s->value] }}</span>
        </a>
    @endforeach
</div>

<form method="GET" class="card mb-4 p-4" x-data>
    @if ($current) <input type="hidden" name="status" value="{{ $current }}"> @endif
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <div class="relative sm:col-span-2">
            <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kode, SN, lokasi, PO…" class="form-control pl-9">
        </div>
        <select name="vendor" class="form-control" @change="$el.form.submit()">
            <option value="">Semua vendor</option>
            @foreach ($vendors as $v)
                <option value="{{ $v->id }}" @selected(($filters['vendor'] ?? '') == $v->id)>{{ $v->name }}</option>
            @endforeach
        </select>
        @if ($branches->isNotEmpty())
            <select name="branch" class="form-control" @change="$el.form.submit()">
                <option value="">Semua lokasi</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected(($filters['branch'] ?? '') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        @endif
        <select name="asset_type" class="form-control" @change="$el.form.submit()">
            <option value="">Hardware & Software</option>
            @foreach ($assetTypes as $t)
                <option value="{{ $t->value }}" @selected(($filters['asset_type'] ?? '') === $t->value)>{{ $t->label() }}</option>
            @endforeach
        </select>
        <select name="license_type" class="form-control" @change="$el.form.submit()">
            <option value="">Semua tipe lisensi</option>
            @foreach ($licenseTypes as $t)
                <option value="{{ $t->value }}" @selected(($filters['license_type'] ?? '') === $t->value)>{{ $t->label() }}</option>
            @endforeach
        </select>
    </div>
    @if (collect($filters)->except('status')->filter()->isNotEmpty())
        <div class="mt-3 flex justify-end">
            <a href="{{ request()->url().($current ? '?status='.$current : '') }}" class="text-xs font-medium text-slate-500 hover:text-brand-600">Reset filter</a>
        </div>
    @endif
</form>
