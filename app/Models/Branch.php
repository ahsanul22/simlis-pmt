<?php

namespace App\Models;

use App\Enums\BranchType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'type', 'region', 'address', 'is_active'];

    protected $casts = [
        'type' => BranchType::class,
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
