<?php

namespace App\Models;

use App\Enums\RenewalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LicenseRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number', 'license_id', 'status',
        'old_name', 'old_license_key', 'old_start_date', 'old_end_date',
        'new_name', 'new_license_key', 'new_start_date', 'new_end_date',
        'estimated_cost', 'actual_cost', 'po_number', 'reason',
        'requested_by', 'submitted_at', 'reviewed_by', 'reviewed_at', 'review_note',
        'completed_by', 'completed_at',
    ];

    protected $hidden = ['old_license_key', 'new_license_key'];

    protected $casts = [
        'status' => RenewalStatus::class,
        'old_license_key' => 'encrypted',
        'new_license_key' => 'encrypted',
        'old_start_date' => 'date',
        'old_end_date' => 'date',
        'new_start_date' => 'date',
        'new_end_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (LicenseRenewal $renewal) {
            if (empty($renewal->request_number)) {
                $prefix = 'RNW-'.now()->format('Y').'-';
                $last = static::where('request_number', 'like', $prefix.'%')->max('request_number');
                $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
                $renewal->request_number = $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class)->withTrashed();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isBranchScoped()) {
            $query->whereHas('license', fn (Builder $q) => $q->where('branch_id', $user->branch_id ?? 0));
        }

        return $query;
    }

    public function scopeStatus(Builder $query, RenewalStatus ...$statuses): Builder
    {
        return $query->whereIn('status', array_map(fn ($s) => $s->value, $statuses));
    }
}
