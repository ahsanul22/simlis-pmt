<x-nav-link :href="route('admin-ti.dashboard')" :active="request()->routeIs('admin-ti.dashboard')" icon="home">Dashboard</x-nav-link>

<p class="nav-section">Inventaris</p>
<x-nav-link :href="route('admin-ti.licenses.index')" :active="request()->routeIs('admin-ti.licenses.*')" icon="key" :badge="$menuBadges['licenses'] ?? 0" badge-tone="danger">Lisensi</x-nav-link>
<x-nav-link :href="route('admin-ti.devices.index')" :active="request()->routeIs('admin-ti.devices.*')" icon="server-stack">Perangkat</x-nav-link>

<p class="nav-section">Proses</p>
<x-nav-link :href="route('admin-ti.renewals.index')" :active="request()->routeIs('admin-ti.renewals.*')" icon="arrow-path" :badge="$menuBadges['renewals'] ?? 0">Perpanjangan</x-nav-link>

<p class="nav-section">Analisis</p>
<x-nav-link :href="route('admin-ti.reports.index')" :active="request()->routeIs('admin-ti.reports.*')" icon="chart-bar">Laporan</x-nav-link>
<x-nav-link :href="route('admin-ti.activity-logs.index')" :active="request()->routeIs('admin-ti.activity-logs.*')" icon="clipboard-list">Audit Log</x-nav-link>
