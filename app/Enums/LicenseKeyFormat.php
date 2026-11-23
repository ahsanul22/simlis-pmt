<?php

namespace App\Enums;

/**
 * Bentuk license key sebuah lisensi. Tidak semua lisensi punya key:
 * - Subscription/warranty perangkat (Palo Alto, Sangfor, HPE) cukup terikat ke Serial Number -> NONE.
 * - Sebagian software (mis. Microsoft Office) key-nya berupa foto/gambar (stiker/kartu/screenshot) -> IMAGE,
 *   disimpan sebagai lampiran kategori AttachmentCategory::LICENSE_KEY.
 * - Key berupa teks yang bisa diketik -> TEXT, disimpan terenkripsi di licenses.license_key.
 */
enum LicenseKeyFormat: string
{
    case NONE = 'none';
    case TEXT = 'text';
    case IMAGE = 'image';

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'Tanpa license key',
            self::TEXT => 'Kode teks',
            self::IMAGE => 'Foto / gambar',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::NONE => 'Lisensi terikat ke serial number perangkat / akun portal vendor',
            self::TEXT => 'Key bisa diketik, disimpan terenkripsi',
            self::IMAGE => 'Key berupa foto (stiker, kartu, screenshot), diunggah sebagai file',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NONE => 'minus-circle',
            self::TEXT => 'key',
            self::IMAGE => 'photo',
        };
    }
}
