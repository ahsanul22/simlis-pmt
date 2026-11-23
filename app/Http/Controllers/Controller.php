<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /** Tolak akses jika user ber-scope branch membuka data branch lain. */
    protected function authorizeBranch(Model $model): void
    {
        $user = auth()->user();

        abort_if($user->isBranchScoped() && (int) $model->branch_id !== (int) $user->branch_id, 403, 'Data ini bukan milik branch Anda.');
    }

    /**
     * TODO(tahap-backend): placeholder aksi tulis (store/update/destroy/approve/...).
     * Cari "pendingBackend(" untuk menemukan semua aksi yang belum diimplementasi.
     */
    protected function pendingBackend(): RedirectResponse
    {
        return back()->with('info', 'Tampilan sudah siap. Penyimpanan data akan diaktifkan pada tahap backend.');
    }
}
