{{--
    <x-page-header title="Daftar Lisensi" subtitle="..." :back="route(...)">
        <x-slot:actions> tombol-tombol </x-slot:actions>
    </x-page-header>
--}}
@props(['title', 'subtitle' => null, 'back' => null])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-2 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
        @endif
        <h1 class="truncate text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
