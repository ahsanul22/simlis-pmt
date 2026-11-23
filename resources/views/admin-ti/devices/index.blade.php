@extends('layouts.app')

@section('title', 'Perangkat')

@section('content')
<x-page-header title="Perangkat" subtitle="Inventaris perangkat jaringan & keamanan beserta lisensinya">
    <x-slot:actions>
        <a href="{{ route('admin-ti.devices.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Tambah Perangkat</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" x-data>
    <div class="relative sm:col-span-2 lg:col-span-1">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="SN, hostname, IP…" class="form-control pl-9">
    </div>
    <select name="vendor" class="form-control" @change="$el.form.submit()">
        <option value="">Semua vendor</option>
        @foreach ($vendors as $v)<option value="{{ $v->id }}" @selected(($filters['vendor'] ?? '') == $v->id)>{{ $v->name }}</option>@endforeach
    </select>
    <select name="model" class="form-control" @change="$el.form.submit()">
        <option value="">Semua model</option>
        @foreach ($models as $m)<option value="{{ $m->id }}" @selected(($filters['model'] ?? '') == $m->id)>{{ $m->name }}</option>@endforeach
    </select>
    <select name="branch" class="form-control" @change="$el.form.submit()">
        <option value="">Semua lokasi</option>
        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected(($filters['branch'] ?? '') == $b->id)>{{ $b->name }}</option>@endforeach
    </select>
    <select name="status" class="form-control" @change="$el.form.submit()">
        <option value="">Semua status</option>
        @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>@endforeach
    </select>
</form>

@include('shared.devices._table', ['showRoute' => 'admin-ti.devices.show', 'editRoute' => 'admin-ti.devices.edit'])
@endsection
