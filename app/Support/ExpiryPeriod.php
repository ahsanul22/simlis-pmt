<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Rentang "masa berakhir" lisensi dari filter bulan/tahun:
 *   from_month + from_year  s/d  to_month + to_year
 * Bulan boleh kosong → dianggap satu tahun penuh (Jan untuk "dari", Des untuk "sampai").
 * Tahun kosong → sisi itu tidak dibatasi. Bila terbalik, otomatis ditukar.
 *
 * Dipakai di daftar lisensi (License::scopeFilter) dan laporan/RAB (App\Services\LicenseReport).
 */
final class ExpiryPeriod
{
    public const KEYS = ['from_month', 'from_year', 'to_month', 'to_year'];

    private function __construct(public readonly ?Carbon $start, public readonly ?Carbon $end) {}

    public static function fromArray(array $f): self
    {
        $month = fn ($v) => filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]) ?: null;
        $year = fn ($v) => filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]) ?: null;

        $start = ($y = $year($f['from_year'] ?? null))
            ? Carbon::create($y, $month($f['from_month'] ?? null) ?? 1, 1)->startOfDay()
            : null;
        $end = ($y = $year($f['to_year'] ?? null))
            ? Carbon::create($y, $month($f['to_month'] ?? null) ?? 12, 1)->endOfMonth()->startOfDay()
            : null;

        if ($start && $end && $start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfMonth(), $start->copy()->endOfMonth()->startOfDay()];
        }

        return new self($start, $end);
    }

    public function isSet(): bool
    {
        return $this->start !== null || $this->end !== null;
    }

    /** Batasi query lisensi ke end_date dalam rentang (lisensi tanpa end date otomatis keluar). */
    public function apply(Builder $query, string $column = 'end_date'): Builder
    {
        if (! $this->isSet()) {
            return $query;
        }

        return $query->whereNotNull($column)
            ->when($this->start, fn (Builder $q) => $q->whereDate($column, '>=', $this->start->toDateString()))
            ->when($this->end, fn (Builder $q) => $q->whereDate($column, '<=', $this->end->toDateString()));
    }

    /** Mis. "Januari 2027 – Desember 2027", "Mulai Maret 2027", "Semua periode". */
    public function label(): string
    {
        $fmt = fn (Carbon $d) => $d->translatedFormat('F Y');

        return match (true) {
            $this->start && $this->end => $fmt($this->start) === $fmt($this->end)
                ? $fmt($this->start)
                : $fmt($this->start).' – '.$fmt($this->end),
            $this->start !== null => 'Mulai '.$fmt($this->start),
            $this->end !== null => 'Sampai '.$fmt($this->end),
            default => 'Semua periode',
        };
    }

    /** Nilai filter untuk tombol cepat (Tahun ini, Tahun depan, 12 bulan ke depan). */
    public static function presets(): array
    {
        $now = now();
        $in11 = $now->copy()->addMonthsNoOverflow(11);

        return [
            'Tahun ini' => ['from_month' => 1, 'from_year' => $now->year, 'to_month' => 12, 'to_year' => $now->year],
            'Tahun depan' => ['from_month' => 1, 'from_year' => $now->year + 1, 'to_month' => 12, 'to_year' => $now->year + 1],
            '12 bulan ke depan' => ['from_month' => $now->month, 'from_year' => $now->year, 'to_month' => $in11->month, 'to_year' => $in11->year],
        ];
    }

    /** Pilihan tahun di dropdown: dari tahun end_date terlama s/d terjauh (+ tahun berjalan & 3 tahun ke depan). */
    public static function yearOptions(): array
    {
        $range = DB::table('licenses')->whereNotNull('end_date')
            ->selectRaw('min(end_date) as min_end, max(end_date) as max_end')->first();

        $now = (int) now()->year;
        $min = $range?->min_end ? min((int) substr($range->min_end, 0, 4), $now - 1) : $now - 1;
        $max = $range?->max_end ? max((int) substr($range->max_end, 0, 4), $now + 3) : $now + 3;

        return range($min, $max);
    }

    /** [1 => 'Januari', ..., 12 => 'Desember'] sesuai locale aplikasi. */
    public static function monthOptions(): array
    {
        return collect(range(1, 12))->mapWithKeys(fn (int $m) => [$m => Carbon::create(2000, $m, 1)->translatedFormat('F')])->all();
    }
}
