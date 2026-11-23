{{-- 4 kartu ringkasan status lisensi. Variabel: $summary --}}
@php
    $licenseIndex = \Illuminate\Support\Facades\Route::has(auth()->user()->role->routePrefix().'.licenses.index')
        ? fn ($status = null) => role_route('licenses.index', array_filter(['status' => $status]))
        : fn () => null;
    $t = \App\Models\Setting::expiryThresholds();
@endphp

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card label="Total Lisensi Aktif" :value="$summary['total']" icon="key" tone="brand" :href="$licenseIndex()"
                 :hint="$summary['active'].' aman (> '.$t['warning'].' hari)'" />
    <x-stat-card label="Akan Habis" :value="$summary['warning']" icon="clock" tone="warning" :href="$licenseIndex('warning')"
                 :hint="'Berakhir dalam '.$t['critical'].'–'.$t['warning'].' hari'" />
    <x-stat-card label="Kritis" :value="$summary['critical']" icon="exclamation-triangle" tone="orange" :href="$licenseIndex('critical')"
                 :hint="'Berakhir ≤ '.$t['critical'].' hari'" />
    <x-stat-card label="Expired" :value="$summary['expired']" icon="x-circle" tone="danger" :href="$licenseIndex('expired')"
                 hint="Sudah melewati end date" />
</div>
