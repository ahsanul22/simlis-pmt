<?php

namespace App\Enums;

enum LicenseType: string
{
    case SUBSCRIPTION = 'subscription';   // mis. Palo Alto Threat Prevention, Sangfor IAG
    case SUPPORT = 'support';             // kontrak support / maintenance
    case WARRANTY = 'warranty';           // mis. HPE Foundation Care
    case PERPETUAL = 'perpetual';         // software beli putus (tanpa end date)
    case SAAS = 'saas';                   // software berlangganan per user/seat

    public function label(): string
    {
        return match ($this) {
            self::SUBSCRIPTION => 'Subscription',
            self::SUPPORT => 'Support / Maintenance',
            self::WARRANTY => 'Warranty',
            self::PERPETUAL => 'Perpetual',
            self::SAAS => 'SaaS / Per User',
        };
    }

    public function hasExpiry(): bool
    {
        return $this !== self::PERPETUAL;
    }
}
