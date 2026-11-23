{{-- Layout halaman tamu (login). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.head')
</head>
<body class="min-h-screen bg-white dark:bg-slate-950">
    @yield('content')

    @include('includes.scripts')
</body>
</html>
