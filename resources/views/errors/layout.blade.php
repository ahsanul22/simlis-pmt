{{-- Layout halaman error. Section: code, title, message --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('includes.head')
</head>
<body class="flex min-h-screen items-center justify-center px-4">
    <div class="text-center">
        <p class="text-6xl font-bold text-brand-600">@yield('code')</p>
        <h1 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">@yield('title')</h1>
        <p class="mt-2 max-w-md text-slate-500 dark:text-slate-400">@yield('message')</p>
        <div class="mt-8 flex justify-center gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-secondary"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali</a>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary">Ke Dashboard</a>
        </div>
    </div>
</body>
</html>
