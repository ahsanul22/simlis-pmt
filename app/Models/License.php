<?php

namespace App\Models;

use App\Enums\AssetType;
use App\Enums\ExpiryStatus;
use App\Enums\AttachmentCategory;
use App\Enums\LicenseKeyFormat;
use App\Enums\LicenseType;
use App\Enums\RenewalStatus;
use App\Models\Concerns\ScopedByBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class License extends Model
{
    use HasFactory, ScopedByBranch, SoftDeletes;

    protected $fillable = [
        'code', 'asset_type', 'license_type', 'vendor_id', 'branch_id', 'device_id', 'software_product_id',
        'name', 'key_format', 'license_key', 'seats', 'seats_used', 'start_date', 'end_date',
        'cost', 'currency', 'contract_number', 'po_number', 'supplier',
        'is_terminated', 'notes', 'created_by', 'updated_by',
    ];

    protected $hidden = ['license_key'];

    protected $attributes = ['key_format' => 'none'];

    protected $casts = [
        'asset_type' => AssetType::class,
        'license_type' => LicenseType::class,
        'key_format' => LicenseKeyFormat::class,
        'license_key' => 'encrypted',
        'start_date' => 'date',
        'end_date' => 'date',
        'cost' => 'decimal:2',
        'is_terminated' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (License $license) {
            if (empty($license->code)) {
                $license->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $prefix = 'LIC-'.now()->format('Y').'-';
        $last = static::withTrashed()->where('code', 'like', $prefix.'%')->max('code');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    // ---------------------------------------------------------------- Relasi

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function softwareProduct(): BelongsTo
    {
        return $this->belongsTo(SoftwareProduct::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(LicenseRenewal::class)->latest();
    }

    /** Pengajuan perpanjangan yang masih berjalan (maks. satu). */
    public function openRenewal(): HasOne
    {
        return $this->hasOne(LicenseRenewal::class)->whereIn('status', RenewalStatus::openStatuses())->latestOfMany();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** Foto/gambar license key (key_format = image). Hanya untuk role dengan izin license.view_key. */
    public function keyImages(): MorphMany
    {
        return $this->attachments()->where('category', AttachmentCategory::LICENSE_KEY);
    }

    /** Apakah key sudah tercatat sesuai formatnya (null = lisensi memang tidak memakai key). */
    public function keyIsRecorded(): ?bool
    {
        return match ($this->key_format) {
            LicenseKeyFormat::NONE => null,
            LicenseKeyFormat::TEXT => filled($this->license_key),
            LicenseKeyFormat::IMAGE => $this->relationLoaded('attachments')
                ? $this->attachments->contains(fn ($a) => $a->category === AttachmentCategory::LICENSE_KEY)
                : $this->keyImages()->exists(),
        };
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(LicenseReminderLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ---------------------------------------------------------------- Atribut hitung

    /** Sisa hari sampai end_date (negatif = sudah lewat). Null untuk perpetual. */
    protected function daysRemaining(): Attribute
    {
        return Attribute::get(fn () => $this->end_date
            ? (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false)
            : null);
    }

    protected function expiryStatus(): Attribute
    {
        return Attribute::get(function () {
            $t = Setting::expiryThresholds();

            return ExpiryStatus::fromDaysRemaining($this->days_remaining, $t['warning'], $t['critical']);
        });
    }

    // ---------------------------------------------------------------- Scope

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_terminated', false);
    }

    /** Lisensi yang akan habis dalam N hari ke depan (belum expired). */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->whereNotNull('end_date')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereDate('end_date', '<=', now()->addDays($days)->toDateString());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('end_date')->whereDate('end_date', '<', now()->toDateString());
    }

    /**
     * Filter dari query string halaman daftar lisensi.
     * Key: q, vendor, branch, asset_type, license_type, status
     */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('licenses.code', 'like', $like)
                    ->orWhere('licenses.name', 'like', $like)
                    ->orWhere('licenses.po_number', 'like', $like)
                    ->orWhereHas('device', fn (Builder $d) => $d->where('serial_number', 'like', $like)->orWhere('hostname', 'like', $like))
                    ->orWhereHas('branch', fn (Builder $b) => $b->where('name', 'like', $like))
                    ->orWhereHas('softwareProduct', fn (Builder $s) => $s->where('name', 'like', $like)));
            })
            ->when($f['vendor'] ?? null, fn (Builder $q, $v) => $q->where('vendor_id', $v))
            ->when($f['branch'] ?? null, fn (Builder $q, $v) => $q->where('branch_id', $v))
            ->when($f['asset_type'] ?? null, fn (Builder $q, $v) => $q->where('asset_type', $v))
            ->when($f['license_type'] ?? null, fn (Builder $q, $v) => $q->where('license_type', $v))
            ->when(ExpiryStatus::tryFrom($f['status'] ?? ''), fn (Builder $q, ExpiryStatus $s) => $q->whereExpiryStatus($s))
            ->tap(fn (Builder $q) => \App\Support\ExpiryPeriod::fromArray($f)->apply($q, 'licenses.end_date'));
    }

    /** Filter berdasarkan ExpiryStatus (untuk tabel & dashboard). */
    public function scopeWhereExpiryStatus(Builder $query, ExpiryStatus $status): Builder
    {
        $t = Setting::expiryThresholds();
        $today = now()->toDateString();

        return match ($status) {
            ExpiryStatus::PERPETUAL => $query->whereNull('end_date'),
            ExpiryStatus::EXPIRED => $query->expired(),
            ExpiryStatus::CRITICAL => $query->expiringWithin($t['critical']),
            ExpiryStatus::WARNING => $query->whereNotNull('end_date')
                ->whereDate('end_date', '>', now()->addDays($t['critical'])->toDateString())
                ->whereDate('end_date', '<=', now()->addDays($t['warning'])->toDateString()),
            ExpiryStatus::ACTIVE => $query->whereNotNull('end_date')
                ->whereDate('end_date', '>', now()->addDays($t['warning'])->toDateString()),
        };
    }
}
