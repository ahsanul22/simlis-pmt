<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('username', 'superadmin')->firstOrFail();
        $this->actingAs($this->admin);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'username' => ' Budi.Santoso ',
            'email' => 'Budi@Gmail.com',
            'nip' => '',
            'phone' => '0812-3456-7890',
            'role' => UserRole::PIC_BRANCH->value,
            'branch_id' => Branch::first()->id,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_create_user(): void
    {
        $this->post(route('super-admin.users.store'), $this->payload())
            ->assertRedirect(route('super-admin.users.index'))
            ->assertSessionHas('success');

        $user = User::where('username', 'budi.santoso')->firstOrFail();
        $this->assertSame('budi@gmail.com', $user->email);
        $this->assertNull($user->nip);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertDatabaseHas('activity_logs', ['event' => 'user.created', 'subject_id' => $user->id]);
    }

    public function test_create_validation(): void
    {
        $this->from(route('super-admin.users.create'))
            ->post(route('super-admin.users.store'), $this->payload([
                'username' => 'admin.ti',          // sudah dipakai
                'email' => 'bukan-email',
                'branch_id' => '',                  // wajib untuk PIC Branch
                'password' => 'pendek',
                'password_confirmation' => 'beda',
            ]))
            ->assertRedirect(route('super-admin.users.create'))
            ->assertSessionHasErrors(['username', 'email', 'branch_id', 'password']);
    }

    public function test_update_email_without_changing_password(): void
    {
        $user = User::where('username', 'admin.ti')->firstOrFail();
        $oldHash = $user->password;

        $this->put(route('super-admin.users.update', $user), $this->payload([
            'name' => $user->name, 'username' => $user->username, 'email' => 'admin.ti.baru@gmail.com',
            'role' => $user->role->value, 'branch_id' => $user->branch_id, 'phone' => $user->phone,
            'nip' => $user->nip, 'password' => '', 'password_confirmation' => '',
        ]))->assertRedirect(route('super-admin.users.index'))->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('admin.ti.baru@gmail.com', $user->email);
        $this->assertSame($oldHash, $user->password);
        $this->assertDatabaseHas('activity_logs', ['event' => 'user.updated', 'subject_id' => $user->id]);
    }

    public function test_cannot_demote_or_deactivate_self_or_delete_self(): void
    {
        $self = $this->payload([
            'name' => $this->admin->name, 'username' => $this->admin->username, 'email' => $this->admin->email,
            'role' => UserRole::ADMIN_TI->value, 'branch_id' => '', 'password' => '', 'password_confirmation' => '',
        ]);

        $this->put(route('super-admin.users.update', $this->admin), $self)->assertSessionHasErrors('role');
        $this->put(route('super-admin.users.update', $this->admin), ['is_active' => '0', 'role' => 'super_admin'] + $self)
            ->assertSessionHasErrors('is_active');

        $this->delete(route('super-admin.users.destroy', $this->admin))->assertSessionHas('error');
        $this->assertNotSoftDeleted($this->admin);
        $this->assertSame(UserRole::SUPER_ADMIN, $this->admin->fresh()->role);
    }

    public function test_delete_user_is_soft_delete_and_username_stays_reserved(): void
    {
        $user = User::where('username', 'pic.makassar')->firstOrFail();

        $this->delete(route('super-admin.users.destroy', $user))->assertSessionHas('success');
        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('activity_logs', ['event' => 'user.deleted', 'subject_id' => $user->id]);

        $this->post(route('super-admin.users.store'), $this->payload(['username' => 'pic.makassar']))
            ->assertSessionHasErrors('username');
    }

    public function test_deactivated_user_is_logged_out(): void
    {
        $user = User::where('username', 'manajemen')->firstOrFail();
        $user->update(['is_active' => false]);

        $this->actingAs($user)->get(route('manajemen.dashboard'))->assertRedirect(route('login'));
    }

    public function test_other_roles_cannot_manage_users(): void
    {
        $this->actingAs(User::where('username', 'admin.ti')->first())
            ->post(route('super-admin.users.store'), $this->payload())
            ->assertForbidden();
    }
}
