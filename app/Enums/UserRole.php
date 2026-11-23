<?php

namespace App\Enums;

/**
 * Role pengguna sistem monitoring lisensi.
 *
 * - SUPER_ADMIN : pengelola sistem (user, master data, pengaturan reminder).
 * - ADMIN_TI    : operator TI pusat, kelola perangkat & lisensi semua lokasi, eksekusi perpanjangan.
 * - PIC_BRANCH  : penanggung jawab TI di branch/terminal, hanya lokasinya sendiri, mengajukan perpanjangan.
 * - MANAJEMEN   : pimpinan/approver, melihat semua data & laporan, menyetujui/menolak pengajuan.
 */
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_TI = 'admin_ti';
    case PIC_BRANCH = 'pic_branch';
    case MANAJEMEN = 'manajemen';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN_TI => 'Admin TI',
            self::PIC_BRANCH => 'PIC Branch',
            self::MANAJEMEN => 'Manajemen',
        };
    }

    /**
     * Matriks hak akses. Dipakai Gate/Policy (backend) dan untuk menyembunyikan menu (frontend).
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => ['*'],
            self::ADMIN_TI => [
                'dashboard.view', 'report.view', 'report.export',
                'device.view', 'device.manage',
                'license.view', 'license.manage', 'license.view_key',
                'renewal.view', 'renewal.request', 'renewal.complete',
                'attachment.manage', 'audit.view',
            ],
            self::PIC_BRANCH => [
                'dashboard.view',
                'device.view', 'license.view',
                'renewal.view', 'renewal.request',
                'attachment.manage',
            ],
            self::MANAJEMEN => [
                'dashboard.view', 'report.view', 'report.export',
                'device.view', 'license.view',
                'renewal.view', 'renewal.approve',
                'audit.view',
            ],
        };
    }

    public function can(string $permission): bool
    {
        $perms = $this->permissions();

        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    /**
     * Prefix URL, nama route, dan folder view/controller per role:
     * super-admin | admin-ti | manajemen | pic-branch
     */
    public function routePrefix(): string
    {
        return str_replace('_', '-', $this->value);
    }

    public function dashboardRoute(): string
    {
        return $this->routePrefix().'.dashboard';
    }

    public function badge(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-400/30',
            self::ADMIN_TI => 'bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/30',
            self::MANAJEMEN => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/30',
            self::PIC_BRANCH => 'bg-teal-50 text-teal-700 ring-teal-600/20 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-400/30',
        };
    }

    /** Role yang datanya dibatasi hanya pada branch miliknya. */
    public function isBranchScoped(): bool
    {
        return $this === self::PIC_BRANCH;
    }

    public static function options(): array
    {
        return array_map(fn (self $r) => ['value' => $r->value, 'label' => $r->label()], self::cases());
    }
}
