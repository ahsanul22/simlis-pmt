@props(['href', 'active' => false, 'icon' => null, 'badge' => 0, 'badgeTone' => 'brand'])

@php
    $badgeClass = match ($badgeTone) {
        'danger' => 'bg-rose-500 text-white',
        'warning' => 'bg-amber-400 text-slate-900',
        default => $active ? 'bg-white/20 text-white' : 'bg-brand-500/20 text-brand-200',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->class(['nav-link', 'active' => $active]) }} @if ($active) aria-current="page" @endif>
    @if ($icon)
        <x-icon :name="$icon" class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-slate-400' }}" />
    @endif
    <span class="flex-1 truncate">{{ $slot }}</span>
    @if ($badge > 0)
        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">{{ $badge }}</span>
    @endif
</a>
