<?php

use Illuminate\Support\Carbon;

if (! function_exists('tgl')) {
    /** Format tanggal Indonesia. tgl($date) => "10 Okt 2027", tgl($date, 'd F Y') => "10 Oktober 2027" */
    function tgl($date, string $format = 'd M Y'): string
    {
        return $date ? Carbon::parse($date)->translatedFormat($format) : '-';
    }
}

if (! function_exists('tgl_waktu')) {
    function tgl_waktu($date): string
    {
        return $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') : '-';
    }
}

if (! function_exists('rupiah')) {
    function rupiah($amount): string
    {
        return $amount === null || $amount === '' ? '-' : 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('sisa_hari')) {
    /** 45 => "45 hari lagi", 0 => "Hari ini", -3 => "Lewat 3 hari", null => "Tanpa batas" */
    function sisa_hari(?int $days): string
    {
        return match (true) {
            $days === null => 'Tanpa batas',
            $days === 0 => 'Berakhir hari ini',
            $days < 0 => 'Lewat '.abs($days).' hari',
            default => $days.' hari lagi',
        };
    }
}

if (! function_exists('role_route')) {
    /** Route milik role user yang login: role_route('licenses.show', $id) => admin-ti.licenses.show */
    function role_route(string $name, mixed $parameters = []): string
    {
        return route(auth()->user()->role->routePrefix().'.'.$name, $parameters);
    }
}

if (! function_exists('per_page')) {
    /** Jumlah baris per halaman dari ?per_page= (dibatasi ke pilihan yang tersedia). */
    function per_page(int $default = 15): int
    {
        $value = (int) request('per_page', $default);

        return in_array($value, PER_PAGE_OPTIONS, true) ? $value : $default;
    }
}

if (! defined('PER_PAGE_OPTIONS')) {
    define('PER_PAGE_OPTIONS', [15, 25, 50, 100]);
}
