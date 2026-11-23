<?php

namespace App\Models;

use App\Enums\DeviceCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceModel extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'name', 'category', 'description', 'is_active'];

    protected $casts = [
        'category' => DeviceCategory::class,
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
