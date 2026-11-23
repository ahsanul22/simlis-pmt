{{--
    Tabel perangkat. Variabel: $devices, $showRoute (nullable), $editRoute (nullable)
--}}
@php
    $scoped = auth()->user()->isBranchScoped();
    $showRoute ??= null;
    $editRoute ??= null;
@endphp

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Perangkat</th>
                    <th>Serial Number</th>
                    @unless ($scoped)<th>Lokasi</th>@endunless
                    <th>Jaringan</th>
                    <th>Status</th>
                    <th>Lisensi</th>
                    @if ($showRoute || $editRoute)<th class="text-right">Aksi</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($devices as $device)
                    @php
                        $worst = $device->licenses->sortBy(fn ($l) => $l->days_remaining ?? PHP_INT_MAX)->first();
                    @endphp
                    <tr>
                        <td>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $device->model->name }}</p>
                            <p class="text-xs text-slate-500">{{ $device->model->vendor->name }}</p>
                        </td>
                        <td class="font-mono text-xs">{{ $device->serial_number }}</td>
                        @unless ($scoped)<td>{{ $device->branch->name }}</td>@endunless
                        <td class="text-xs">
                            <p>{{ $device->hostname ?: '-' }}</p>
                            <p class="font-mono text-slate-500">{{ $device->ip_address }}</p>
                        </td>
                        <td><x-badge :enum="$device->status" /></td>
                        <td>
                            @if ($worst)
                                <div class="flex items-center gap-2">
                                    <x-badge :enum="$worst->expiry_status" />
                                    @if ($device->licenses->count() > 1)<span class="text-xs text-slate-400">+{{ $device->licenses->count() - 1 }}</span>@endif
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500">{{ sisa_hari($worst->days_remaining) }}</p>
                            @else
                                <span class="text-xs text-slate-400">Belum ada lisensi</span>
                            @endif
                        </td>
                        @if ($showRoute || $editRoute)
                            <td class="text-right">
                                @if ($showRoute)<a href="{{ route($showRoute, $device) }}" class="btn btn-ghost btn-sm" title="Detail"><x-icon name="eye" class="h-4 w-4" /></a>@endif
                                @if ($editRoute)<a href="{{ route($editRoute, $device) }}" class="btn btn-ghost btn-sm" title="Ubah"><x-icon name="pencil" class="h-4 w-4" /></a>@endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="server-stack" title="Tidak ada perangkat">Coba ubah filter pencarian.</x-empty-state></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($devices->total() > min(PER_PAGE_OPTIONS))
        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $devices->links() }}</div>
    @endif
</div>
