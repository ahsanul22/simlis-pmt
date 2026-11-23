<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'website',
        'support_contact_name', 'support_email', 'support_phone',
        'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function deviceModels(): HasMany
    {
        return $this->hasMany(DeviceModel::class);
    }

    public function softwareProducts(): HasMany
    {
        return $this->hasMany(SoftwareProduct::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
