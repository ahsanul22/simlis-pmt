@extends('layouts.app')

@section('title', 'Ajukan Perpanjangan')

@section('content')
<x-page-header title="Ajukan Perpanjangan" :back="route('pic-branch.licenses.show', $license)"
               :subtitle="$license->code.' · '.$license->name" />

@include('shared.renewals._request-form')
@endsection
