{{-- <x-stat-card label="Total Lisensi" :value="55" icon="key" tone="brand" hint="..." :href="..." /> --}}
@props(['label', 'value', 'icon' => 'key', 'tone' => 'brand', 'hint' => null, 'href' => null])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'orange' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
        'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['card group flex items-start gap-4 p-5', 'transition hover:border-brand-300 hover:shadow-md dark:hover:border-brand-500/50' => $href]) }}>
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
        @endif
    </div>
    @if ($href)
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-500 dark:text-slate-600" />
    @endif
</{{ $tag }}>
