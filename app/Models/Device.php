<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use App\Models\Concerns\ScopedByBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, ScopedByBranch, SoftDeletes;

    protected $fillable = [
        'branch_id', 'device_model_id', 'serial_number', 'hostname', 'ip_address',
        'status', 'installed_at', 'notes', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'status' => DeviceStatus::class,
        'installed_at' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class, 'device_model_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** Filter halaman daftar perangkat. Key: q, vendor, branch, model, status */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('serial_number', 'like', $like)
                    ->orWhere('hostname', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('branch', fn (Builder $b) => $b->where('name', 'like', $like)));
            })
            ->when($f['vendor'] ?? null, fn (Builder $q, $v) => $q->whereHas('model', fn (Builder $m) => $m->where('vendor_id', $v)))
            ->when($f['branch'] ?? null, fn (Builder $q, $v) => $q->where('branch_id', $v))
            ->when($f['model'] ?? null, fn (Builder $q, $v) => $q->where('device_model_id', $v))
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v));
    }
}
