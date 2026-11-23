@extends('layouts.app')

@section('title', 'Produk Software')

@section('content')
<x-page-header title="Produk Software" subtitle="Katalog software yang lisensinya dipantau">
    <x-slot:actions>
        <a href="{{ route('super-admin.software-products.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Produk</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-3" x-data>
    <div class="relative sm:col-span-2">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Nama produk…" class="form-control pl-9">
    </div>
    <select name="vendor" class="form-control" @change="$el.form.submit()">
        <option value="">Semua vendor</option>
        @foreach ($vendors as $v)<option value="{{ $v->id }}" @selected(request('vendor') == $v->id)>{{ $v->name }}</option>@endforeach
    </select>
</form>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Produk</th><th>Vendor</th><th>Kategori</th><th class="text-center">Lisensi</th><th class="text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($products as $p)
                    <tr>
                        <td><p class="font-medium text-slate-900 dark:text-white">{{ $p->name }}</p><p class="text-xs text-slate-500">{{ $p->edition }}</p></td>
                        <td>{{ $p->vendor->name }}</td>
                        <td>{{ $p->category ?? '-' }}</td>
                        <td class="text-center">{{ $p->licenses_count }}</td>
                        <td class="text-right">
                            <a href="{{ route('super-admin.software-products.edit', $p) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /></a>
                            <x-delete-button :action="route('super-admin.software-products.destroy', $p)" :item="'produk '.$p->name" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <x-empty-state icon="code-bracket" title="Belum ada produk software">
                            Tambahkan produk seperti Microsoft 365, Windows Server, atau antivirus agar lisensinya bisa dipantau.
                        </x-empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($products->total() > min(PER_PAGE_OPTIONS))<div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $products->links() }}</div>@endif
</div>
@endsection
