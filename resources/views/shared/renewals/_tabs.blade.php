{{-- Tab status pengajuan. Variabel: $tabs (key => label), $tab, $tabCounts --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <nav class="flex gap-1 overflow-x-auto rounded-xl bg-slate-100 p-1 dark:bg-slate-800/60">
        @foreach ($tabs as $key => $label)
            <a href="{{ request()->url().'?tab='.$key }}"
               class="flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition {{ $tab === $key ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                {{ $label }}
                <span class="rounded-full bg-slate-200 px-1.5 text-[11px] dark:bg-slate-700">{{ $tabCounts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>
    <form method="GET" class="relative w-full sm:w-72">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. pengajuan, lokasi, SN…" class="form-control pl-9">
    </form>
</div>
