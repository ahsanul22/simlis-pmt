{{-- Isi halaman audit log. Variabel: $logs, $users, $events --}}
<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" x-data>
    <div class="relative lg:col-span-2">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari deskripsi aktivitas…" class="form-control pl-9">
    </div>
    <select name="user" class="form-control" @change="$el.form.submit()">
        <option value="">Semua pengguna</option>
        @foreach ($users as $u)
            <option value="{{ $u->id }}" @selected(request('user') == $u->id)>{{ $u->name }}</option>
        @endforeach
    </select>
    <select name="event" class="form-control" @change="$el.form.submit()">
        <option value="">Semua aktivitas</option>
        @foreach ($events as $key => $label)
            <option value="{{ $key }}" @selected(request('event') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <input type="date" name="from" value="{{ request('from') }}" class="form-control" title="Dari tanggal" @change="$el.form.submit()">
        <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="Sampai tanggal" @change="$el.form.submit()">
    </div>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Keterangan</th><th>IP</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-xs">{{ tgl_waktu($log->created_at) }}</td>
                        <td>
                            <p class="font-medium">{{ $log->user?->name ?? 'Sistem' }}</p>
                            @if ($log->user)<p class="text-xs text-slate-500">{{ $log->user->role->label() }}</p>@endif
                        </td>
                        <td><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">{{ $log->event }}</code></td>
                        <td class="max-w-md whitespace-normal">{{ $log->description ?? '-' }}</td>
                        <td class="font-mono text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="clipboard-list" title="Belum ada aktivitas tercatat" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->total() > min(PER_PAGE_OPTIONS))
        <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $logs->links() }}</div>
    @endif
</div>
