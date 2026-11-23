<?php

namespace App\Enums;

enum AttachmentCategory: string
{
    case LICENSE_KEY = 'license_key';           // foto/gambar license key (sensitif)
    case LICENSE_CERTIFICATE = 'license_certificate';
    case DEVICE_PHOTO = 'device_photo';
    case PURCHASE_ORDER = 'purchase_order';
    case INVOICE = 'invoice';
    case CONTRACT = 'contract';
    case BAST = 'bast';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LICENSE_KEY => 'Foto License Key',
            self::LICENSE_CERTIFICATE => 'Sertifikat / Bukti Lisensi',
            self::DEVICE_PHOTO => 'Foto Perangkat / Stiker SN',
            self::PURCHASE_ORDER => 'Purchase Order',
            self::INVOICE => 'Invoice',
            self::CONTRACT => 'Kontrak',
            self::BAST => 'BAST',
            self::OTHER => 'Lainnya',
        };
    }

    /** Lampiran sensitif hanya boleh dilihat role dengan izin license.view_key. */
    public function isSensitive(): bool
    {
        return $this === self::LICENSE_KEY;
    }

    /** Kategori yang boleh dipilih user (yang sensitif hanya untuk pemegang license.view_key). */
    public static function optionsFor(\App\Models\User $user): array
    {
        return collect(self::cases())
            ->reject(fn (self $c) => $c->isSensitive() && ! $user->hasPermission('license.view_key'))
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
