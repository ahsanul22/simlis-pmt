{{--
    Tabel pengajuan perpanjangan.
    Variabel: $renewals, $showRoute (nama route relatif role, mis. 'renewals.show' / 'approvals.show')
--}}
@php $scoped = auth()->user()->isBranchScoped(); @endphp

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pengajuan</th>
                    <th>Lisensi</th>
                    @unless ($scoped)<th>Lokasi</th>@endunless
                    <th>Periode Baru</th>
                    <th>Diajukan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($renewals as $r)
                    <tr>
                        <td class="font-mono text-xs font-medium text-slate-900 dark:text-white">{{ $r->request_number }}</td>
                        <td>
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $r->license->device?->model->name ?? $r->license->softwareProduct?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $r->new_name ?? $r->license->name }}</p>
                        </td>
                        @unless ($scoped)<td>{{ $r->license->branch->name }}</td>@endunless
                        <td class="text-xs">{{ tgl($r->new_start_date) }} – {{ tgl($r->new_end_date) }}</td>
                        <td class="text-xs">
                            <p>{{ $r->requester?->name ?? '-' }}</p>
                            <p class="text-slate-400">{{ tgl($r->submitted_at ?? $r->created_at) }}</p>
                        </td>
                        <td><x-badge :enum="$r->status" /></td>
                        <td class="text-right">
                            <a href="{{ role_route($showRoute, $r) }}" class="btn btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><x-empty-state icon="arrow-path" title="Tidak ada pengajuan di tab ini" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($renewals->total() > min(PER_PAGE_OPTIONS))
        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $renewals->links() }}</div>
    @endif
</div>
