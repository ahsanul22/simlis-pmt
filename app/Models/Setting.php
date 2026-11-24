<?php

namespace App\Models;

use App\Enums\ExpiryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const EXPIRY_THRESHOLDS = 'expiry_thresholds';
    public const REMINDER_DAYS = 'reminder_days';
    public const REMINDER_MAIL = 'reminder_mail';

    protected $fillable = ['key', 'value', 'description'];

    protected $casts = [
        'value' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn (Setting $s) => Cache::forget('setting:'.$s->key));
        static::deleted(fn (Setting $s) => Cache::forget('setting:'.$s->key));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('setting:'.$key, fn () => static::where('key', $key)->value('value')) ?? $default;
    }

    public static function put(string $key, mixed $value, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            array_filter(['value' => $value, 'description' => $description], fn ($v) => $v !== null)
        );
    }

    /** @return array{warning:int, critical:int} */
    public static function expiryThresholds(): array
    {
        $v = static::get(self::EXPIRY_THRESHOLDS, []);

        return [
            'warning' => (int) ($v['warning'] ?? ExpiryStatus::DEFAULT_WARNING_DAYS),
            'critical' => (int) ($v['critical'] ?? ExpiryStatus::DEFAULT_CRITICAL_DAYS),
        ];
    }

    /** Hari-hari sebelum end_date saat reminder dikirim, mis. [90, 60, 30, 14, 7, 1]. */
    public static function reminderDays(): array
    {
        return array_map('intval', static::get(self::REMINDER_DAYS, [90, 60, 30, 14, 7, 1]));
    }

    /** Placeholder yang bisa dipakai di template email reminder → keterangannya. */
    public const REMINDER_MAIL_PLACEHOLDERS = [
        '{nama}' => 'Nama penerima',
        '{jumlah}' => 'Jumlah lisensi di email',
        '{judul}' => 'Judul otomatis, mis. "Lisensi akan habis dalam 14 hari"',
        '{aplikasi}' => 'Nama aplikasi (SIMLIS)',
    ];

    public const REMINDER_MAIL_DEFAULTS = [
        'subject' => '[{aplikasi}] {judul}',
        'greeting' => 'Halo {nama},',
        'intro' => 'Berikut {jumlah} lisensi yang mendekati akhir masa berlakunya. Mohon segera ditindaklanjuti (pengadaan / pengajuan perpanjangan).',
        'closing' => "Salam,\nDivisi Teknologi Informasi",
    ];

    /** Template email reminder (teks bebas dari Super Admin, tabel lisensi tetap otomatis). @return array{subject:string, greeting:string, intro:string, closing:string} */
    public static function reminderMail(): array
    {
        $saved = (array) static::get(self::REMINDER_MAIL, []);
        $template = [];

        foreach (self::REMINDER_MAIL_DEFAULTS as $key => $default) {
            $template[$key] = filled($saved[$key] ?? null) ? (string) $saved[$key] : $default;
        }

        return $template;
    }
}
