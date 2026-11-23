{{--
    Tabel lisensi. Aksi menyesuaikan hak akses user login.
    Variabel: $licenses (paginator / collection)
--}}
@php
    $user = auth()->user();
    $canManage = $user->hasPermission('license.manage');
    $canRequest = $user->hasPermission('renewal.request');
@endphp

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Lisensi</th>
                    <th>Aset</th>
                    @unless ($user->isBranchScoped())<th>Lokasi</th>@endunless
                    <th>Masa Berlaku</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($licenses as $license)
                    <tr>
                        <td>
                            <a href="{{ role_route('licenses.show', $license) }}" class="font-medium text-slate-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">{{ $license->name }}</a>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                <span class="font-mono">{{ $license->code }}</span> · {{ $license->vendor->name }} · {{ $license->license_type->label() }}
                            </p>
                        </td>
                        <td>@include('shared.licenses._asset-cell', ['license' => $license])</td>
                        @unless ($user->isBranchScoped())
                            <td>{{ $license->branch->name }}</td>
                        @endunless
                        <td><x-period-bar :license="$license" /></td>
                        <td>
                            <x-badge :enum="$license->expiry_status" />
                            @if ($license->openRenewal)
                                <p class="mt-1 flex items-center gap-1 text-[11px] text-brand-600 dark:text-brand-400">
                                    <x-icon name="arrow-path" class="h-3 w-3" /> {{ $license->openRenewal->status->label() }}
                                </p>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($canRequest && ! $license->openRenewal && $license->end_date)
                                    <a href="{{ role_route('renewals.create', $license) }}" class="btn btn-secondary btn-sm" title="Ajukan perpanjangan">
                                        <x-icon name="arrow-path" class="h-4 w-4" /><span class="hidden xl:inline">Perpanjang</span>
                                    </a>
                                @endif
                                <a href="{{ role_route('licenses.show', $license) }}" class="btn btn-ghost btn-sm" title="Detail"><x-icon name="eye" class="h-4 w-4" /></a>
                                @if ($canManage)
                                    <a href="{{ role_route('licenses.edit', $license) }}" class="btn btn-ghost btn-sm" title="Ubah"><x-icon name="pencil" class="h-4 w-4" /></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state icon="key" title="Tidak ada lisensi">Coba ubah kata kunci atau filter pencarian.</x-empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($licenses instanceof \Illuminate\Contracts\Pagination\Paginator && $licenses->total() > min(PER_PAGE_OPTIONS))
        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $licenses->links() }}</div>
    @endif
</div>
