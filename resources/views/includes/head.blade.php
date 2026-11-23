<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Sistem Monitoring Lisensi Hardware & Software TI">

<title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231c5ef1' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z'/%3E%3C/svg%3E">

{{-- Terapkan tema sebelum render agar tidak berkedip --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
