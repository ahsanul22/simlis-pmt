<?php

namespace App\Enums;

/**
 * Alur perpanjangan:
 * DRAFT -> SUBMITTED (diajukan PIC Branch / Admin TI)
 *       -> APPROVED / REJECTED (oleh Manajemen)
 *       -> COMPLETED (Admin TI input lisensi baru; periode lama otomatis jadi riwayat)
 * CANCELLED bisa dari DRAFT/SUBMITTED oleh pengaju.
 */
enum RenewalStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::DRAFT, self::CANCELLED => 'bg-slate-100 text-slate-700 ring-slate-500/20 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/30',
            self::SUBMITTED => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/30',
            self::APPROVED => 'bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/30',
            self::REJECTED => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/30',
            self::COMPLETED => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30',
        };
    }

    /** Status yang masih "berjalan" — satu lisensi hanya boleh punya satu pengajuan terbuka. */
    public static function openStatuses(): array
    {
        return [self::DRAFT->value, self::SUBMITTED->value, self::APPROVED->value];
    }
}
