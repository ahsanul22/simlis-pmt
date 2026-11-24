{{--
    Kartu yang bisa dibuka-tutup. Butuh state Alpine di induk: isOpen(id) & toggle(id).
    <x-accordion-section id="email" title="Template Email" icon="envelope" tone="violet">
        <x-slot:summary>Ringkasan saat tertutup (boleh berisi x-text)</x-slot:summary>
        ...isi...
    </x-accordion-section>
--}}
@props(['id', 'title', 'icon' => 'cog', 'tone' => 'brand', 'description' => null])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    ];
@endphp

<section {{ $attributes->class('card overflow-hidden') }} data-section="{{ $id }}">
    <button type="button" @click="toggle('{{ $id }}')" :aria-expanded="isOpen('{{ $id }}')" aria-controls="section-{{ $id }}"
            class="flex w-full items-center gap-4 px-5 py-4 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</span>
            @isset($summary)
                <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">{{ $summary }}</span>
            @endisset
        </span>
        <span class="shrink-0 text-slate-400 transition-transform duration-200 dark:text-slate-500" :class="isOpen('{{ $id }}') && 'rotate-180'">
            <x-icon name="chevron-down" class="h-5 w-5" />
        </span>
    </button>

    <div id="section-{{ $id }}" x-show="isOpen('{{ $id }}')" x-collapse x-cloak>
        <div class="border-t border-slate-200 p-5 dark:border-slate-800">
            @if ($description)
                <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</section>
