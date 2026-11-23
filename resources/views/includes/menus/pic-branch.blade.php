<x-nav-link :href="route('pic-branch.dashboard')" :active="request()->routeIs('pic-branch.dashboard')" icon="home">Dashboard</x-nav-link>

<p class="nav-section">Branch Saya</p>
<x-nav-link :href="route('pic-branch.licenses.index')" :active="request()->routeIs('pic-branch.licenses.*')" icon="key" :badge="$menuBadges['licenses'] ?? 0" badge-tone="danger">Lisensi</x-nav-link>
<x-nav-link :href="route('pic-branch.devices.index')" :active="request()->routeIs('pic-branch.devices.*')" icon="server-stack">Perangkat</x-nav-link>

<p class="nav-section">Pengajuan</p>
<x-nav-link :href="route('pic-branch.renewals.index')" :active="request()->routeIs('pic-branch.renewals.*')" icon="arrow-path" :badge="$menuBadges['renewals'] ?? 0" badge-tone="danger">Perpanjangan Saya</x-nav-link>
