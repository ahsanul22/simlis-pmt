{{-- Sidebar: menu dimuat dari includes/menus/{role-prefix}.blade.php --}}
<div x-show="sidebarOpen" x-transition.opacity x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

<aside class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0"
       :class="sidebarOpen && 'translate-x-0'">
    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/5 px-5">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white p-1">
            <img src="{{ asset('images/logo-pelindo-mark.png') }}" alt="Logo Pelindo" class="h-full w-full object-contain">
        </span>
        <div class="leading-tight">
            <p class="text-sm font-bold tracking-wide text-white">{{ config('app.name') }}</p>
            <p class="text-[11px] text-slate-400">Monitoring Lisensi TI</p>
        </div>
        <button type="button" class="ml-auto text-slate-400 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
            <x-icon name="x-mark" class="h-5 w-5" />
        </button>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @include('includes.menus.'.$authUser->role->routePrefix())
    </nav>

    {{-- Info akun --}}
    <div class="border-t border-white/5 p-4">
        <div class="rounded-lg bg-white/5 px-3 py-2.5">
            <p class="text-[11px] uppercase tracking-wider text-slate-500">Masuk sebagai</p>
            <p class="truncate text-sm font-medium text-white">{{ $authUser->role->label() }}</p>
            @if ($authUser->branch)
                <p class="mt-0.5 flex items-center gap-1 truncate text-xs text-slate-400">
                    <x-icon name="map-pin" class="h-3.5 w-3.5" /> {{ $authUser->branch->name }}
                </p>
            @endif
        </div>
    </div>
</aside>
