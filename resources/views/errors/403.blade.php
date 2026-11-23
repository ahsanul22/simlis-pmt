@extends('errors.layout')

@section('title', 'Akses ditolak')
@section('code', '403')
@section('message', $exception->getMessage() ?: 'Anda tidak memiliki izin untuk membuka halaman ini.')
