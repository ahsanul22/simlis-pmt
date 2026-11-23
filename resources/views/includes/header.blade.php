@php
    $searchRoute = $authUser->hasPermission('license.view') && \Illuminate\Support\Facades\Route::has($authUser->role->routePrefix().'.licenses.index')
        ? route($authUser->role->routePrefix().'.licenses.index')
        : null;
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
        style="padding-top: env(safe-area-inset-top, 0px)">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button type="button" class="btn-ghost -ml-1 rounded-lg p-2 lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
            <x-icon name="bars-3" class="h-5 w-5" />
        </button>

        {{-- Pencarian global lisensi --}}
        @if ($searchRoute)
            <form action="{{ $searchRoute }}" method="GET" class="hidden max-w-md flex-1 sm:block">
                <label class="relative block">
                    <span class="sr-only">Cari lisensi</span>
                    <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari SN, lokasi, kode lisensi…"
                           class="form-control border-transparent bg-slate-100 pl-9 shadow-none focus:bg-white dark:border-transparent dark:bg-slate-800 dark:focus:bg-slate-900">
                </label>
            </form>
        @endif

        <div class="ml-auto flex items-center gap-1">
            {{-- Toggle tema --}}
            <button type="button" class="btn-ghost rounded-lg p-2" @click="$store.theme.toggle()" aria-label="Ganti tema">
                <x-icon name="moon" class="h-5 w-5" x-show="!$store.theme.dark" />
                <x-icon name="sun" class="h-5 w-5" x-show="$store.theme.dark" x-cloak />
            </button>

            {{-- Notifikasi --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="btn-ghost relative rounded-lg p-2" @click="open = !open" aria-label="Notifikasi">
                    <x-icon name="bell" class="h-5 w-5" />
                    @if ($unreadCount > 0)
                        <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </button>
                <div x-show="open" x-transition.origin.top.right x-cloak
                     class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                        <p class="text-sm font-semibold">Notifikasi</p>
                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">Tandai semua dibaca</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
                        @forelse ($latestNotifications as $n)
                            <a href="{{ route('notifications.show', $n->id) }}" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $n->data['message'] ?? '' }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                            </a>
                        @empty
                            <div class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                <x-icon name="bell" class="mx-auto mb-2 h-6 w-6 text-slate-300 dark:text-slate-600" />
                                Tidak ada notifikasi baru
                            </div>
                        @endforelse
                    </div>
                    <a href="{{ route('notifications.index') }}" class="block border-t border-slate-100 px-4 py-2.5 text-center text-xs font-medium text-brand-600 hover:bg-slate-50 dark:border-slate-800 dark:text-brand-400 dark:hover:bg-slate-800/60">
                        Lihat semua notifikasi
                    </a>
                </div>
            </div>

            {{-- Menu user --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800" @click="open = !open">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">
                        {{ \Illuminate\Support\Str::of($authUser->name)->explode(' ')->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}
                    </span>
                    <span class="hidden text-left leading-tight md:block">
                        <span class="block text-sm font-medium text-slate-800 dark:text-slate-100">{{ $authUser->name }}</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $authUser->role->label() }}</span>
                    </span>
                    <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 md:block" />
                </button>
                <div x-show="open" x-transition.origin.top.right x-cloak
                     class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900">
                    <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                        <p class="truncate text-sm font-medium">{{ $authUser->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $authUser->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-icon name="user-circle" class="h-4 w-4 text-slate-400" /> Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex w-full items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                            <x-icon name="arrow-right-on-rectangle" class="h-4 w-4" /> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
