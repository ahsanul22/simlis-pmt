@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<x-page-header title="Audit Log" subtitle="Jejak seluruh aktivitas pengguna di sistem" />

@include('shared.activity-logs._index')
@endsection
