<?php

namespace App\View\Composers;

use App\Enums\ExpiryStatus;
use App\Enums\RenewalStatus;
use App\Enums\UserRole;
use App\Models\License;
use App\Models\LicenseRenewal;
use Illuminate\View\View;

/**
 * Data untuk includes.header & includes.sidebar: user login, notifikasi, angka badge menu.
 */
class LayoutComposer
{
    private static ?array $cache = null;

    public function compose(View $view): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        self::$cache ??= [
            'authUser' => $user->loadMissing('branch'),
            'unreadCount' => $user->unreadNotifications()->count(),
            'latestNotifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
            'menuBadges' => $this->menuBadges($user),
        ];

        $view->with(self::$cache);
    }

    private function menuBadges($user): array
    {
        $expiring = License::visibleTo($user)->active()->whereExpiryStatus(ExpiryStatus::CRITICAL)->count()
            + License::visibleTo($user)->active()->whereExpiryStatus(ExpiryStatus::EXPIRED)->count();

        return match ($user->role) {
            UserRole::ADMIN_TI => [
                'licenses' => $expiring,
                'renewals' => LicenseRenewal::status(RenewalStatus::APPROVED)->count(),
            ],
            UserRole::MANAJEMEN => [
                'approvals' => LicenseRenewal::status(RenewalStatus::SUBMITTED)->count(),
            ],
            UserRole::PIC_BRANCH => [
                'licenses' => $expiring,
                'renewals' => LicenseRenewal::visibleTo($user)->status(RenewalStatus::REJECTED)->count(),
            ],
            default => [],
        };
    }
}
