<?php

namespace App\Models;

use App\Enums\ExpiryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const EXPIRY_THRESHOLDS = 'expiry_thresholds';
    public const REMINDER_DAYS = 'reminder_days';

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
}
