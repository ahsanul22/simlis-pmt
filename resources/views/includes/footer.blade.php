<footer class="border-t border-slate-200 px-4 py-4 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-1 sm:flex-row">
        <p>&copy; {{ now()->year }} {{ config('app.name') }} — Sistem Monitoring Lisensi TI</p>
        <p>Waktu server: {{ now()->translatedFormat('l, d F Y H:i') }} WIB</p>
    </div>
</footer>
