@extends('layouts.app')

@section('title', 'Lokasi / Branch')

@section('content')
<x-page-header title="Lokasi / Branch" subtitle="Kantor pusat, branch, dan terminal">
    <x-slot:actions>
        <a href="{{ route('super-admin.branches.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Lokasi</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-3" x-data>
    <div class="relative sm:col-span-2">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nama atau kode lokasi…" class="form-control pl-9">
    </div>
    <select name="type" class="form-control" @change="$el.form.submit()">
        <option value="">Semua tipe</option>
        @foreach ($types as $t)<option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>@endforeach
    </select>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Wilayah</th><th class="text-center">Perangkat</th><th class="text-center">Lisensi</th><th class="text-center">User</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($branches as $b)
                    <tr>
                        <td class="font-mono text-xs font-semibold">{{ $b->code }}</td>
                        <td class="font-medium text-slate-900 dark:text-white">{{ $b->name }}</td>
                        <td>{{ $b->type->label() }}</td>
                        <td>{{ $b->region ?? '-' }}</td>
                        <td class="text-center">{{ $b->devices_count }}</td>
                        <td class="text-center">{{ $b->licenses_count }}</td>
                        <td class="text-center">{{ $b->users_count }}</td>
                        <td>{!! $b->is_active ? '<span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300">Aktif</span>' : '<span class="badge bg-slate-100 text-slate-600 ring-slate-500/20">Nonaktif</span>' !!}</td>
                        <td class="text-right">
                            <a href="{{ route('super-admin.branches.edit', $b) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /></a>
                            <x-delete-button :action="route('super-admin.branches.destroy', $b)" :item="'lokasi '.$b->name" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="building-office" title="Tidak ada lokasi" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($branches->total() > min(PER_PAGE_OPTIONS))<div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $branches->links() }}</div>@endif
</div>
@endsection
