{{--
    Tombol hapus dengan konfirmasi (tanpa confirm() browser).
    <x-delete-button :action="route('...destroy', $item)" item="Branch Bima" />
--}}
@props(['action', 'item' => 'data ini', 'label' => null, 'variant' => 'outline'])

<div x-data="{ confirm: false }" class="inline-block">
    <button type="button" @click="confirm = true" {{ $attributes->class([match (true) { ! $label => 'btn btn-ghost btn-sm text-rose-600 dark:text-rose-400', $variant === 'solid' => 'btn btn-danger', default => 'btn btn-secondary text-rose-600 dark:text-rose-400' }]) }} title="Hapus">
        <x-icon name="trash" class="h-4 w-4" />
        @if ($label) <span>{{ $label }}</span> @endif
    </button>

    <div x-show="confirm" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirm = false"></div>
        <div x-show="confirm" x-transition class="relative w-full max-w-sm rounded-2xl bg-white p-6 text-left shadow-xl dark:bg-slate-900">
            <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                <x-icon name="exclamation-triangle" class="h-6 w-6" />
            </span>
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Hapus {{ $item }}?</h3>
            <p class="mt-1 text-sm whitespace-normal text-slate-500 dark:text-slate-400">Data akan dipindahkan ke arsip dan tidak tampil lagi di daftar.</p>
            <form method="POST" action="{{ $action }}" class="mt-5 flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-secondary" @click="confirm = false">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, hapus</button>
            </form>
        </div>
    </div>
</div>
