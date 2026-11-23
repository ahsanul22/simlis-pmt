@props(['icon' => 'inbox', 'title' => 'Belum ada data'])

<div {{ $attributes->class(['flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $slot }}</div>
    @endif
</div>
