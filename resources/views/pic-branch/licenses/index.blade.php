@extends('layouts.app')

@section('title', 'Lisensi Branch')

@section('content')
<x-page-header title="Lisensi Branch" :subtitle="'Lisensi aktif di '.(auth()->user()->branch?->name ?? '-')" />

@include('shared.licenses._filters')
@include('shared.licenses._table')
@endsection
