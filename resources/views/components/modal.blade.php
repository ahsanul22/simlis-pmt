{{--
    Modal Alpine. Buka: $dispatch('open-modal', 'nama-modal'). Tutup: $dispatch('close-modal', 'nama-modal') / Esc.
    <x-modal name="reject" title="Tolak Pengajuan"> ... <x-slot:footer>...</x-slot:footer> </x-modal>
--}}
@props(['name', 'title' => null, 'maxWidth' => 'max-w-lg', 'show' => false])

<div x-data="{ show: @js($show) }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show" x-cloak
     class="fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center"
     role="dialog" aria-modal="true">
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="show = false"></div>

    <div x-show="show" x-transition
         class="relative w-full {{ $maxWidth }} overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
        @if ($title)
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" @click="show = false" aria-label="Tutup">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>
        @endif
        <div class="px-5 py-4">{{ $slot }}</div>
        @isset($footer)
            <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3 dark:border-slate-800 dark:bg-slate-900/60">{{ $footer }}</div>
        @endisset
    </div>
</div>
