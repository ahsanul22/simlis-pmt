<x-nav-link :href="route('manajemen.dashboard')" :active="request()->routeIs('manajemen.dashboard')" icon="home">Dashboard</x-nav-link>

<p class="nav-section">Persetujuan</p>
<x-nav-link :href="route('manajemen.approvals.index')" :active="request()->routeIs('manajemen.approvals.*')" icon="check-badge" :badge="$menuBadges['approvals'] ?? 0" badge-tone="warning">Pengajuan Perpanjangan</x-nav-link>

<p class="nav-section">Monitoring</p>
<x-nav-link :href="route('manajemen.licenses.index')" :active="request()->routeIs('manajemen.licenses.*')" icon="key">Daftar Lisensi</x-nav-link>
<x-nav-link :href="route('manajemen.reports.index')" :active="request()->routeIs('manajemen.reports.*')" icon="chart-bar">Laporan</x-nav-link>
<x-nav-link :href="route('manajemen.activity-logs.index')" :active="request()->routeIs('manajemen.activity-logs.*')" icon="clipboard-list">Audit Log</x-nav-link>
