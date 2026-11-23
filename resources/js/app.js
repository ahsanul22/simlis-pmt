import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

// ---------------------------------------------------------------------------
// Tema gelap/terang (disimpan di localStorage, default ikut sistem)
// ---------------------------------------------------------------------------
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try { localStorage.setItem('theme', this.dark ? 'dark' : 'light'); } catch (e) {}
        window.dispatchEvent(new CustomEvent('theme-changed'));
    },
});

// ---------------------------------------------------------------------------
// Komponen chart reusable: <canvas x-data="chart(@js($config))">
// config = { type, labels, datasets: [{ label, data, colors? }] }
// ---------------------------------------------------------------------------
Alpine.data('chart', (config) => ({
    instance: null,
    init() {
        this.render();
        window.addEventListener('theme-changed', () => this.render());
    },
    render() {
        const dark = document.documentElement.classList.contains('dark');
        const text = dark ? '#94a3b8' : '#64748b';
        const grid = dark ? 'rgba(148,163,184,0.12)' : 'rgba(100,116,139,0.12)';
        const isCircular = ['doughnut', 'pie'].includes(config.type);

        if (this.instance) this.instance.destroy();
        this.instance = new Chart(this.$el, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets: config.datasets.map((d) => ({
                    ...d,
                    backgroundColor: d.colors ?? d.backgroundColor,
                    borderColor: isCircular ? (dark ? '#0f172a' : '#ffffff') : (d.colors ?? d.borderColor),
                    borderWidth: isCircular ? 2 : 0,
                    borderRadius: config.type === 'bar' ? 6 : 0,
                    maxBarThickness: 36,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: config.type === 'doughnut' ? '70%' : undefined,
                plugins: {
                    legend: {
                        display: config.legend ?? isCircular,
                        position: 'bottom',
                        labels: { color: text, usePointStyle: true, boxWidth: 8, padding: 16 },
                    },
                    tooltip: { padding: 10, cornerRadius: 8 },
                },
                scales: isCircular ? {} : {
                    x: { stacked: config.stacked ?? false, ticks: { color: text }, grid: { display: false } },
                    y: { stacked: config.stacked ?? false, beginAtZero: true, ticks: { color: text, precision: 0 }, grid: { color: grid } },
                },
            },
        });
    },
}));

window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();
