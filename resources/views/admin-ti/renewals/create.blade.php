@extends('layouts.app')

@section('title', 'Ajukan Perpanjangan')

@section('content')
<x-page-header title="Ajukan Perpanjangan" :back="route('admin-ti.licenses.show', $license)"
               :subtitle="$license->code.' · '.$license->name.' · '.$license->branch->name" />

@include('shared.renewals._request-form')
@endsection
