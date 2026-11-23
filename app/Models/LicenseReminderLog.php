<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseReminderLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['license_id', 'end_date', 'threshold_days', 'channel', 'recipients_count', 'sent_at'];

    protected $casts = [
        'end_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
