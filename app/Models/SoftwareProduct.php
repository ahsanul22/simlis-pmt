<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareProduct extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'name', 'edition', 'category', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
