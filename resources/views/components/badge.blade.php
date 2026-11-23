{{--
    Badge dari enum yang punya label() & badge(): <x-badge :enum="$license->expiry_status" />
    Atau manual: <x-badge class="bg-slate-100 ...">Teks</x-badge>
--}}
@props(['enum' => null, 'dot' => true])

<span {{ $attributes->class(['badge', $enum?->badge() ?? 'bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/30']) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    @endif
    {{ $enum?->label() ?? $slot }}
</span>
