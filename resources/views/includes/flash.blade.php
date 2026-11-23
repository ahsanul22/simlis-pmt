{{-- Pesan flash dari session: success | error | warning | info --}}
@php
    $flashTypes = [
        'success' => ['check-circle', 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200'],
        'error' => ['x-circle', 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200'],
        'warning' => ['exclamation-triangle', 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200'],
        'info' => ['information-circle', 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200'],
    ];
@endphp

@foreach ($flashTypes as $type => [$icon, $classes])
    @if (session($type))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $classes }}" role="alert">
            <x-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="flex-1">{{ session($type) }}</p>
            <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Tutup">
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>
    @endif
@endforeach
