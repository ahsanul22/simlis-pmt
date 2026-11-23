<?php

namespace App\Enums;

enum DeviceStatus: string
{
    case ACTIVE = 'active';
    case SPARE = 'spare';
    case RMA = 'rma';
    case DECOMMISSIONED = 'decommissioned';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Beroperasi',
            self::SPARE => 'Cadangan',
            self::RMA => 'Dalam Perbaikan (RMA)',
            self::DECOMMISSIONED => 'Tidak Digunakan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30',
            self::SPARE => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-400/30',
            self::RMA => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/30',
            self::DECOMMISSIONED => 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-500/10 dark:text-slate-400 dark:ring-slate-400/30',
        };
    }
}
