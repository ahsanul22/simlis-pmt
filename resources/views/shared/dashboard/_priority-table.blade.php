{{--
    Daftar lisensi prioritas (expired / kritis / akan habis).
    Variabel: $priorityLicenses, $title (opsional)
--}}
@php $user = auth()->user(); @endphp

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">{{ $title ?? 'Perlu Tindakan' }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Lisensi expired, kritis, dan akan habis — terdekat lebih dulu</p>
        </div>
        @if (\Illuminate\Support\Facades\Route::has($user->role->routePrefix().'.licenses.index'))
            <a href="{{ role_route('licenses.index') }}" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">Lihat semua</a>
        @endif
    </div>
    @if ($priorityLicenses->isEmpty())
        <x-empty-state icon="shield-check" title="Semua lisensi aman">Tidak ada lisensi yang akan habis dalam waktu dekat.</x-empty-state>
    @else
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($priorityLicenses as $license)
                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 py-3.5 sm:flex-nowrap">
                    <span class="h-10 w-1 shrink-0 rounded-full" style="background: {{ $license->expiry_status->color() }}"></span>
                    <div class="min-w-0 flex-1">
                        <a href="{{ role_route('licenses.show', $license) }}" class="block truncate text-sm font-medium text-slate-900 hover:text-brand-600 dark:text-white">
                            {{ $license->device?->model->name ?? $license->softwareProduct?->name }} — {{ $license->branch->name }}
                        </a>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $license->vendor->name }} · {{ $license->name }}</p>
                    </div>
                    <div class="w-44 shrink-0"><x-period-bar :license="$license" /></div>
                    <div class="shrink-0">
                        @if ($license->openRenewal)
                            <x-badge :enum="$license->openRenewal->status" />
                        @elseif ($user->hasPermission('renewal.request'))
                            <a href="{{ role_route('renewals.create', $license) }}" class="btn btn-primary btn-sm">Perpanjang</a>
                        @else
                            <x-badge :enum="$license->expiry_status" />
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
