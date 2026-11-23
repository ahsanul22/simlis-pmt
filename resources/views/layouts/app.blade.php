{{--
    Layout utama halaman setelah login.
    Section : title (judul tab), content (isi halaman)
    Stack   : styles, scripts
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.head')
</head>
<body class="min-h-screen" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    @include('includes.sidebar')

    <div class="flex min-h-screen flex-col lg:pl-64">
        @include('includes.header')

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @include('includes.flash')
                @yield('content')
            </div>
        </main>

        @include('includes.footer')
    </div>

    @include('includes.scripts')
</body>
</html>
