<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun demo per role. Password semua: "password" — WAJIB diganti di production. */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kp = Branch::where('code', 'KP')->value('id');

        $users = [
            ['superadmin', 'Super Administrator', UserRole::SUPER_ADMIN, $kp],
            ['admin.ti', 'Admin TI Pusat', UserRole::ADMIN_TI, $kp],
            ['manajemen', 'Manajer TI', UserRole::MANAJEMEN, $kp],
            ['pic.bima', 'PIC TI Branch Bima', UserRole::PIC_BRANCH, Branch::where('code', 'BIM')->value('id')],
            ['pic.makassar', 'PIC TI Branch Makassar', UserRole::PIC_BRANCH, Branch::where('code', 'MKS')->value('id')],
        ];

        foreach ($users as [$username, $name, $role, $branchId]) {
            User::updateOrCreate(['username' => $username], [
                'name' => $name,
                'email' => $username.'@spmt.local',
                'role' => $role,
                'branch_id' => $branchId,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => 'password',
            ]);
        }
    }
}
