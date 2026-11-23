<x-nav-link :href="route('super-admin.dashboard')" :active="request()->routeIs('super-admin.dashboard')" icon="home">Dashboard</x-nav-link>

<p class="nav-section">Pengguna</p>
<x-nav-link :href="route('super-admin.users.index')" :active="request()->routeIs('super-admin.users.*')" icon="users">Manajemen User</x-nav-link>

<p class="nav-section">Master Data</p>
<x-nav-link :href="route('super-admin.branches.index')" :active="request()->routeIs('super-admin.branches.*')" icon="building-office">Lokasi / Branch</x-nav-link>
<x-nav-link :href="route('super-admin.vendors.index')" :active="request()->routeIs('super-admin.vendors.*')" icon="tag">Vendor</x-nav-link>
<x-nav-link :href="route('super-admin.device-models.index')" :active="request()->routeIs('super-admin.device-models.*')" icon="cpu-chip">Model Perangkat</x-nav-link>
<x-nav-link :href="route('super-admin.software-products.index')" :active="request()->routeIs('super-admin.software-products.*')" icon="code-bracket">Produk Software</x-nav-link>

<p class="nav-section">Sistem</p>
<x-nav-link :href="route('super-admin.settings.index')" :active="request()->routeIs('super-admin.settings.*')" icon="adjustments">Pengaturan</x-nav-link>
<x-nav-link :href="route('super-admin.activity-logs.index')" :active="request()->routeIs('super-admin.activity-logs.*')" icon="clipboard-list">Audit Log</x-nav-link>
