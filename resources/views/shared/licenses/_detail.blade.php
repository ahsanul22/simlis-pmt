{{--
    Isi halaman detail lisensi (dipakai Admin TI, Manajemen, PIC Branch).
    Variabel: $license, $siblingLicenses
--}}
@php
    $user = auth()->user();
    $status = $license->expiry_status;
    $days = $license->days_remaining;
    $canViewKey = $user->hasPermission('license.view_key');
    $deviceRoute = $user->role->routePrefix().'.devices.show';
    $keyFormat = $license->key_format;
    $keyImages = $license->attachments->filter(fn ($a) => $a->category->isSensitive());
    $otherAttachments = $license->attachments->reject(fn ($a) => $a->category->isSensitive());
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        {{-- Masa berlaku --}}
        <div class="card overflow-hidden">
            <div class="h-1.5" style="background: {{ $status->color() }}"></div>
            <div class="card-body">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Sisa masa berlaku</p>
                        <p class="mt-1 text-3xl font-bold tracking-tight" style="color: {{ $status->color() }}">{{ sisa_hari($days) }}</p>
                    </div>
                    <x-badge :enum="$status" class="px-3 py-1 text-sm" />
                </div>
                <x-period-bar :license="$license" bare class="mt-5" />
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400">Mulai</p>
                        <p class="font-medium">{{ tgl($license->start_date, 'd F Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-slate-500 dark:text-slate-400">Berakhir</p>
                        <p class="font-medium">{{ $license->end_date ? tgl($license->end_date, 'd F Y') : 'Tanpa batas' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">Informasi Lisensi</h2></div>
            <dl class="grid gap-x-6 gap-y-4 p-5 text-sm sm:grid-cols-2">
                @foreach ([
                    'Kode' => $license->code,
                    'Nama Paket' => $license->name,
                    'Jenis Aset' => $license->asset_type->label(),
                    'Tipe Lisensi' => $license->license_type->label(),
                    'Vendor' => $license->vendor->name,
                    'Lokasi' => $license->branch->name,
                    'Jumlah Seat' => $license->seats ? ($license->seats_used ?? 0).' / '.$license->seats.' terpakai' : '-',
                    'Supplier' => $license->supplier ?: '-',
                    'No. Kontrak' => $license->contract_number ?: '-',
                    'No. PO' => $license->po_number ?: '-',
                    'Nilai' => rupiah($license->cost),
                ] as $label => $value)
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900 dark:text-slate-100">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="sm:col-span-2" x-data="{ reveal: false }">
                    <dt class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                        License Key
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <x-icon :name="$keyFormat->icon()" class="h-3 w-3" /> {{ $keyFormat->label() }}
                        </span>
                    </dt>
                    <dd class="mt-1">
                        @if ($keyFormat === \App\Enums\LicenseKeyFormat::NONE)
                            <span class="text-slate-500 dark:text-slate-400">Tidak memakai license key — {{ lcfirst($keyFormat->description()) }}.</span>
                        @elseif (! $canViewKey)
                            <span class="inline-flex items-center gap-1 text-slate-400"><x-icon name="lock-closed" class="h-4 w-4" /> Hanya untuk Admin TI</span>
                        @elseif ($keyFormat === \App\Enums\LicenseKeyFormat::TEXT)
                            @if ($license->license_key)
                                <div class="flex items-center gap-2">
                                    <code class="rounded bg-slate-100 px-2 py-1 font-mono text-xs dark:bg-slate-800" x-text="reveal ? @js($license->license_key) : '••••••••••••••••'"></code>
                                    <button type="button" class="btn btn-ghost btn-sm" @click="reveal = !reveal">
                                        <span x-text="reveal ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400"><x-icon name="exclamation-triangle" class="h-4 w-4" /> Key belum diinput</span>
                            @endif
                        @else
                            @forelse ($keyImages as $img)
                                <div class="flex items-center gap-2 text-sm">
                                    <x-icon name="photo" class="h-4 w-4 text-slate-400" />
                                    <span class="truncate">{{ $img->original_name }}</span>
                                    <span class="text-xs text-slate-400">{{ tgl($img->created_at) }}</span>
                                </div>
                            @empty
                                <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400"><x-icon name="exclamation-triangle" class="h-4 w-4" /> Foto license key belum diunggah</span>
                            @endforelse
                        @endif
                    </dd>
                </div>
                @if ($license->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500 dark:text-slate-400">Catatan</dt>
                        <dd class="mt-0.5 whitespace-pre-line">{{ $license->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Riwayat perpanjangan --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Riwayat Perpanjangan</h2>
                <span class="text-xs text-slate-500">{{ $license->renewals->count() }} catatan</span>
            </div>
            @if ($license->renewals->isEmpty())
                <x-empty-state icon="arrow-path" title="Belum ada riwayat">Riwayat periode lama akan tersimpan otomatis setiap kali lisensi diperpanjang.</x-empty-state>
            @else
                <ol class="relative space-y-6 p-5">
                    @foreach ($license->renewals as $r)
                        <li class="relative flex gap-4">
                            @unless ($loop->last)
                                <span class="absolute left-[15px] top-9 h-[calc(100%-1rem)] w-px bg-slate-200 dark:bg-slate-800"></span>
                            @endunless
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-4 ring-white dark:ring-slate-900 {{ $r->status->badge() }}">
                                <x-icon :name="$r->status === \App\Enums\RenewalStatus::COMPLETED ? 'check-circle' : 'arrow-path'" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium">{{ $r->request_number }}</p>
                                    <x-badge :enum="$r->status" />
                                </div>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                    {{ tgl($r->old_start_date) }} – {{ tgl($r->old_end_date) }}
                                    <x-icon name="arrow-right" class="mx-1 inline h-3 w-3" />
                                    <span class="font-medium">{{ tgl($r->new_start_date) }} – {{ tgl($r->new_end_date) }}</span>
                                </p>
                                @if ($r->old_name && $r->old_name !== $r->new_name)
                                    <p class="text-xs text-slate-500">Paket: {{ $r->old_name }} → {{ $r->new_name }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">
                                    Diajukan {{ $r->requester?->name ?? '-' }}
                                    @if ($r->completed_at) · Selesai {{ tgl($r->completed_at) }} oleh {{ $r->completer?->name ?? '-' }} @endif
                                </p>
                                @if (\Illuminate\Support\Facades\Route::has($user->role->routePrefix().'.renewals.show'))
                                    <a href="{{ role_route('renewals.show', $r) }}" class="mt-1 inline-block text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">Lihat detail</a>
                                @elseif (\Illuminate\Support\Facades\Route::has($user->role->routePrefix().'.approvals.show'))
                                    <a href="{{ role_route('approvals.show', $r) }}" class="mt-1 inline-block text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">Lihat detail</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        {{-- Aset --}}
        <div class="card">
            <div class="card-header"><h2 class="card-title">{{ $license->device ? 'Perangkat' : 'Produk Software' }}</h2></div>
            <div class="card-body text-sm">
                @if ($license->device)
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800"><x-icon name="server-stack" class="h-5 w-5" /></span>
                        <div>
                            <p class="font-semibold">{{ $license->device->model->name }}</p>
                            <p class="text-xs text-slate-500">{{ $license->device->model->category->label() }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 space-y-2">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Serial Number</dt><dd class="font-mono">{{ $license->device->serial_number }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Hostname</dt><dd>{{ $license->device->hostname ?: '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">IP Address</dt><dd class="font-mono">{{ $license->device->ip_address ?: '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-badge :enum="$license->device->status" /></dd></div>
                    </dl>
                    @if (\Illuminate\Support\Facades\Route::has($deviceRoute))
                        <a href="{{ route($deviceRoute, $license->device) }}" class="btn btn-secondary btn-sm mt-4 w-full">Lihat perangkat</a>
                    @endif
                @elseif ($license->softwareProduct)
                    <p class="font-semibold">{{ $license->softwareProduct->name }} {{ $license->softwareProduct->edition }}</p>
                    <p class="text-xs text-slate-500">{{ $license->softwareProduct->category ?? 'Software' }}</p>
                @endif
            </div>
        </div>

        {{-- Lisensi lain pada perangkat yang sama --}}
        @if ($siblingLicenses->isNotEmpty())
            <div class="card">
                <div class="card-header"><h2 class="card-title">Lisensi lain di perangkat ini</h2></div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($siblingLicenses as $s)
                        <li>
                            <a href="{{ role_route('licenses.show', $s) }}" class="flex items-center justify-between gap-3 px-5 py-3 text-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <span class="truncate">{{ $s->name }}</span>
                                <x-badge :enum="$s->expiry_status" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Lampiran --}}
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dokumen & Bukti</h2>
                @if ($user->hasPermission('attachment.manage'))
                    <button type="button" class="btn btn-ghost btn-sm" @click="$dispatch('open-modal', 'upload-attachment')"><x-icon name="arrow-up-tray" class="h-4 w-4" /> Unggah</button>
                @endif
            </div>
            @include('shared.attachments._list', ['attachments' => $otherAttachments])
        </div>

        {{-- Jejak data --}}
        <div class="card">
            <div class="card-body space-y-2 text-xs text-slate-500 dark:text-slate-400">
                <p>Dibuat {{ tgl_waktu($license->created_at) }} oleh {{ $license->creator?->name ?? 'sistem' }}</p>
                <p>Diperbarui {{ tgl_waktu($license->updated_at) }} oleh {{ $license->updater?->name ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

@if ($user->hasPermission('attachment.manage'))
    @include('shared.attachments._upload-modal')
@endif
