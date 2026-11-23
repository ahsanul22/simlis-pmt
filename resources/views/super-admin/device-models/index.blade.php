@extends('layouts.app')

@section('title', 'Model Perangkat')

@section('content')
<x-page-header title="Model Perangkat" subtitle="Tipe perangkat per vendor (PA-440, IAG 51, HPE 5520, …)">
    <x-slot:actions>
        <a href="{{ route('super-admin.device-models.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Model</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-3" x-data>
    <div class="relative">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nama model…" class="form-control pl-9">
    </div>
    <select name="vendor" class="form-control" @change="$el.form.submit()">
        <option value="">Semua vendor</option>
        @foreach ($vendors as $v)<option value="{{ $v->id }}" @selected(request('vendor') == $v->id)>{{ $v->name }}</option>@endforeach
    </select>
    <select name="category" class="form-control" @change="$el.form.submit()">
        <option value="">Semua kategori</option>
        @foreach ($categories as $c)<option value="{{ $c->value }}" @selected(request('category') === $c->value)>{{ $c->label() }}</option>@endforeach
    </select>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Model</th><th>Vendor</th><th>Kategori</th><th class="text-center">Unit</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($models as $m)
                    <tr>
                        <td><p class="font-medium text-slate-900 dark:text-white">{{ $m->name }}</p><p class="text-xs text-slate-500">{{ $m->description }}</p></td>
                        <td>{{ $m->vendor->name }}</td>
                        <td>{{ $m->category->label() }}</td>
                        <td class="text-center">{{ $m->devices_count }}</td>
                        <td>{!! $m->is_active ? '<span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300">Aktif</span>' : '<span class="badge bg-slate-100 text-slate-600 ring-slate-500/20">Nonaktif</span>' !!}</td>
                        <td class="text-right">
                            <a href="{{ route('super-admin.device-models.edit', $m) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /></a>
                            <x-delete-button :action="route('super-admin.device-models.destroy', $m)" :item="'model '.$m->name" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="cpu-chip" title="Belum ada model perangkat" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($models->total() > min(PER_PAGE_OPTIONS))<div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $models->links() }}</div>@endif
</div>
@endsection
