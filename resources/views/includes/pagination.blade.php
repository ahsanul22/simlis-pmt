{{-- Tampilan pagination default (diset di AppServiceProvider) + pilihan jumlah baris per halaman (?per_page=, lihat helper per_page()) --}}
@if ($paginator->total() > min(PER_PAGE_OPTIONS))
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
            <p>
                Menampilkan <span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                dari <span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span> data
            </p>
            <form method="GET" class="flex items-center gap-2">
                @foreach (request()->except(['page', 'per_page']) as $key => $value)
                    @if (is_scalar($value)) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endif
                @endforeach
                <label for="per_page" class="whitespace-nowrap">Baris</label>
                <select id="per_page" name="per_page" onchange="this.form.submit()" class="form-control w-auto py-1 pr-8 text-sm">
                    @foreach (PER_PAGE_OPTIONS as $n)
                        <option value="{{ $n }}" @selected($paginator->perPage() === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($paginator->hasPages())
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm pointer-events-none opacity-40"><x-icon name="chevron-left" class="h-4 w-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm" aria-label="Sebelumnya"><x-icon name="chevron-left" class="h-4 w-4" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-slate-400">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="btn btn-primary btn-sm min-w-[2rem]">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn btn-ghost btn-sm min-w-[2rem]">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm" aria-label="Berikutnya"><x-icon name="chevron-right" class="h-4 w-4" /></a>
            @else
                <span class="btn btn-secondary btn-sm pointer-events-none opacity-40"><x-icon name="chevron-right" class="h-4 w-4" /></span>
            @endif
        </div>
        @endif
    </nav>
@endif
