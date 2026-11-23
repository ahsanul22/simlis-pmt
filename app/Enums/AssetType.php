<?php

namespace App\Enums;

/** Lisensi melekat ke perangkat hardware, atau ke produk software. */
enum AssetType: string
{
    case HARDWARE = 'hardware';
    case SOFTWARE = 'software';

    public function label(): string
    {
        return match ($this) {
            self::HARDWARE => 'Hardware',
            self::SOFTWARE => 'Software',
        };
    }
}
