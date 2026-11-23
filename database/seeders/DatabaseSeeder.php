<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder hanya untuk data DEMO (akun per role).
 * Data asli (pengaturan, master, perangkat & lisensi dari Excel) masuk lewat migration
 * 2026_11_23_000012_import_data_referensi_excel, jadi cukup `php artisan migrate` di server.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
