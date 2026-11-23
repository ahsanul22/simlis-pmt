@extends('layouts.app')

@section('title', 'Vendor')

@section('content')
<x-page-header title="Vendor" subtitle="Principal hardware & software beserta kontak support">
    <x-slot:actions>
        <a href="{{ route('super-admin.vendors.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Vendor</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="mb-4 max-w-sm">
    <div class="relative">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari vendor…" class="form-control pl-9">
    </div>
</form>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($vendors as $v)
        <div class="card flex flex-col p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ mb_substr($v->name, 0, 2) }}</span>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $v->name }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $v->code }}</p>
                    </div>
                </div>
                @unless ($v->is_active)<span class="badge bg-slate-100 text-slate-600 ring-slate-500/20">Nonaktif</span>@endunless
            </div>
            <dl class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-lg bg-slate-50 py-2 dark:bg-slate-800/50"><dt class="text-slate-500">Model</dt><dd class="text-base font-semibold">{{ $v->device_models_count }}</dd></div>
                <div class="rounded-lg bg-slate-50 py-2 dark:bg-slate-800/50"><dt class="text-slate-500">Software</dt><dd class="text-base font-semibold">{{ $v->software_products_count }}</dd></div>
                <div class="rounded-lg bg-slate-50 py-2 dark:bg-slate-800/50"><dt class="text-slate-500">Lisensi</dt><dd class="text-base font-semibold">{{ $v->licenses_count }}</dd></div>
            </dl>
            <div class="mt-4 space-y-1 text-xs text-slate-500">
                <p class="truncate">Support: {{ $v->support_contact_name ?? '-' }} {{ $v->support_email ? '· '.$v->support_email : '' }}</p>
                @if ($v->website)<p class="truncate">{{ $v->website }}</p>@endif
            </div>
            <div class="mt-4 flex justify-end gap-1 border-t border-slate-100 pt-3 dark:border-slate-800">
                <a href="{{ route('super-admin.vendors.edit', $v) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /> Ubah</a>
                <x-delete-button :action="route('super-admin.vendors.destroy', $v)" :item="'vendor '.$v->name" />
            </div>
        </div>
    @empty
        <div class="card sm:col-span-2 xl:col-span-3"><x-empty-state icon="tag" title="Belum ada vendor" /></div>
    @endforelse
</div>
@if ($vendors->total() > min(PER_PAGE_OPTIONS))<div class="mt-4">{{ $vendors->links() }}</div>@endif
@endsection
