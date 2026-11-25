<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Status "draft" pada pengajuan perpanjangan dihapus: pengajuan langsung berstatus "submitted".
 * - Baris draft (bila ada) dianggap dibatalkan, karena belum pernah dikirim ke Manajemen.
 * - Default kolom status diganti dari 'draft' ke 'submitted' (hanya MySQL/MariaDB; SQLite test tidak perlu).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('license_renewals')->where('status', 'draft')->update(['status' => 'cancelled', 'updated_at' => now()]);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE license_renewals ALTER COLUMN status SET DEFAULT 'submitted'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE license_renewals ALTER COLUMN status SET DEFAULT 'draft'");
        }
    }
};
