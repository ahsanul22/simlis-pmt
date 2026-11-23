<?php

namespace App\Services;

use App\Enums\ExpiryStatus;
use App\Enums\RenewalStatus;
use App\Models\License;
use App\Models\LicenseRenewal;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Statistik lisensi untuk dashboard & laporan, otomatis mengikuti cakupan data user
 * (PIC Branch hanya branch-nya). Status dihitung di PHP dari kolom ringan agar cukup 1 query.
 */
class LicenseStatsService
{
    private Collection $rows;

    public function __construct(private User $user)
    {
        $t = Setting::expiryThresholds();

        $this->rows = License::query()
            ->visibleTo($user)
            ->active()
            ->with(['vendor:id,name', 'branch:id,name'])
            ->get(['id', 'vendor_id', 'branch_id', 'asset_type', 'end_date', 'cost'])
            ->each(function (License $l) use ($t) {
                $l->setAttribute('status_key', ExpiryStatus::fromDaysRemaining($l->days_remaining, $t['warning'], $t['critical'])->value);
            });
    }

    public static function for(User $user): self
    {
        return new self($user);
    }

    /** ['total' => 55, 'active' => 53, 'warning' => 1, ...] */
    public function summary(): array
    {
        $counts = $this->rows->countBy('status_key');
        $out = ['total' => $this->rows->count()];
        foreach (ExpiryStatus::cases() as $s) {
            $out[$s->value] = $counts[$s->value] ?? 0;
        }
        $out['attention'] = $out['warning'] + $out['critical'] + $out['expired'];

        return $out;
    }

    /** Konfigurasi chart donut komposisi status. */
    public function statusChart(): array
    {
        $summary = $this->summary();
        $cases = array_filter(ExpiryStatus::cases(), fn ($s) => $s !== ExpiryStatus::PERPETUAL || $summary['perpetual'] > 0);

        return [
            'type' => 'doughnut',
            'labels' => array_values(array_map(fn ($s) => $s->label(), $cases)),
            'datasets' => [[
                'label' => 'Lisensi',
                'data' => array_values(array_map(fn ($s) => $summary[$s->value], $cases)),
                'colors' => array_values(array_map(fn ($s) => $s->color(), $cases)),
            ]],
        ];
    }

    /** Bar bertumpuk: jumlah lisensi per vendor, dipecah per status. */
    public function vendorChart(): array
    {
        $byVendor = $this->rows->groupBy(fn ($l) => $l->vendor?->name ?? '-')->sortKeys();
        $statuses = [ExpiryStatus::ACTIVE, ExpiryStatus::WARNING, ExpiryStatus::CRITICAL, ExpiryStatus::EXPIRED];

        return [
            'type' => 'bar',
            'stacked' => true,
            'legend' => true,
            'labels' => $byVendor->keys()->all(),
            'datasets' => array_map(fn (ExpiryStatus $s) => [
                'label' => $s->label(),
                'data' => $byVendor->map(fn ($g) => $g->where('status_key', $s->value)->count())->values()->all(),
                'colors' => $s->color(),
            ], $statuses),
        ];
    }

    /** Bar: jumlah lisensi yang berakhir per bulan, N bulan ke depan. */
    public function expiryTimelineChart(int $months = 12): array
    {
        $labels = [];
        $data = [];
        $start = now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $labels[] = $m->translatedFormat('M y');
            $data[] = $this->rows->filter(fn ($l) => $l->end_date && $l->end_date->isSameMonth($m))->count();
        }

        return [
            'type' => 'bar',
            'labels' => $labels,
            'datasets' => [['label' => 'Lisensi berakhir', 'data' => $data, 'colors' => '#337dfc']],
        ];
    }

    /** Ringkasan per branch: [branch => [total, attention]] diurutkan yang paling butuh perhatian. */
    public function byBranch(): Collection
    {
        return $this->rows->groupBy(fn ($l) => $l->branch?->name ?? '-')
            ->map(fn ($g, $name) => [
                'name' => $name,
                'total' => $g->count(),
                'warning' => $g->where('status_key', 'warning')->count(),
                'critical' => $g->where('status_key', 'critical')->count(),
                'expired' => $g->where('status_key', 'expired')->count(),
            ])
            ->sortByDesc(fn ($r) => [$r['expired'], $r['critical'], $r['warning'], $r['total']])
            ->values();
    }

    /** Lisensi prioritas: expired + kritis + akan habis, urut end_date terdekat. */
    public function priorityLicenses(int $limit = 8): Collection
    {
        $ids = $this->rows
            ->whereIn('status_key', ['expired', 'critical', 'warning'])
            ->sortBy('end_date')
            ->take($limit)
            ->pluck('id');

        return License::with(['vendor', 'branch', 'device.model', 'softwareProduct', 'openRenewal'])
            ->whereIn('id', $ids)
            ->orderBy('end_date')
            ->get();
    }

    /** Estimasi biaya perpanjangan lisensi yang berakhir dalam N hari (dari kolom cost). */
    public function upcomingCost(int $days = 90): float
    {
        return (float) $this->rows
            ->filter(fn ($l) => $l->days_remaining !== null && $l->days_remaining >= 0 && $l->days_remaining <= $days)
            ->sum('cost');
    }

    public function renewalCounts(): array
    {
        $counts = LicenseRenewal::visibleTo($this->user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(RenewalStatus::cases())->mapWithKeys(fn ($s) => [$s->value => (int) ($counts[$s->value] ?? 0)])->all();
    }
}
