@extends('layouts.app')

@section('title', 'Perangkat Branch')

@section('content')
<x-page-header title="Perangkat Branch" :subtitle="'Perangkat terdaftar di '.(auth()->user()->branch?->name ?? '-')" />

<form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-3" x-data>
    <div class="relative">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="SN, hostname, IP…" class="form-control pl-9">
    </div>
    <select name="vendor" class="form-control" @change="$el.form.submit()">
        <option value="">Semua vendor</option>
        @foreach ($vendors as $v)<option value="{{ $v->id }}" @selected(($filters['vendor'] ?? '') == $v->id)>{{ $v->name }}</option>@endforeach
    </select>
    <select name="status" class="form-control" @change="$el.form.submit()">
        <option value="">Semua status</option>
        @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>@endforeach
    </select>
</form>

@include('shared.devices._table')

<p class="mt-4 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
    <x-icon name="information-circle" class="h-4 w-4" /> Ada data perangkat yang tidak sesuai? Hubungi Admin TI pusat untuk perubahan inventaris.
</p>
@endsection
