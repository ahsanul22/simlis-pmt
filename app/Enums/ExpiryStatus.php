<?php

namespace App\Enums;

/**
 * Status masa berlaku lisensi, dihitung dari end_date (tidak disimpan di DB).
 * Batas hari diambil dari tabel settings (key: expiry_thresholds), default di bawah.
 */
enum ExpiryStatus: string
{
    case ACTIVE = 'active';       // > warning hari
    case WARNING = 'warning';     // <= 90 hari  (mulai proses pengadaan)
    case CRITICAL = 'critical';   // <= 30 hari  (harus segera diperpanjang)
    case EXPIRED = 'expired';     // < 0 hari
    case PERPETUAL = 'perpetual'; // tidak ada end_date

    public const DEFAULT_WARNING_DAYS = 90;
    public const DEFAULT_CRITICAL_DAYS = 30;

    public static function fromDaysRemaining(?int $days, int $warning = self::DEFAULT_WARNING_DAYS, int $critical = self::DEFAULT_CRITICAL_DAYS): self
    {
        return match (true) {
            $days === null => self::PERPETUAL,
            $days < 0 => self::EXPIRED,
            $days <= $critical => self::CRITICAL,
            $days <= $warning => self::WARNING,
            default => self::ACTIVE,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::WARNING => 'Akan Habis',
            self::CRITICAL => 'Kritis',
            self::EXPIRED => 'Expired',
            self::PERPETUAL => 'Permanen',
        };
    }

    /** Kelas Tailwind untuk badge. */
    public function badge(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30',
            self::WARNING => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/30',
            self::CRITICAL => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-400/30',
            self::EXPIRED => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/30',
            self::PERPETUAL => 'bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/30',
        };
    }

    /** Warna solid (hex) untuk chart & indikator. */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => '#10b981',
            self::WARNING => '#f59e0b',
            self::CRITICAL => '#f97316',
            self::EXPIRED => '#f43f5e',
            self::PERPETUAL => '#94a3b8',
        };
    }
}
