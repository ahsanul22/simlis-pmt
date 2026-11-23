<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Membatasi query sesuai hak lihat user: PIC Branch hanya melihat branch-nya sendiri.
 * Pemakaian: Device::visibleTo($user)->get()
 */
trait ScopedByBranch
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isBranchScoped()) {
            $query->where($this->getTable().'.branch_id', $user->branch_id ?? 0);
        }

        return $query;
    }
}
