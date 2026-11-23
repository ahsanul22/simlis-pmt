<?php

namespace Tests\Feature;

use App\Enums\RenewalStatus;
use App\Models\Device;
use App\Models\License;
use App\Models\LicenseRenewal;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: setiap halaman GET per role bisa dirender (200),
 * role lain ditolak (403), dan PIC Branch tidak bisa membuka data branch lain.
 */
class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    public function test_login_page_renders_and_guest_is_redirected(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke akun Anda');
        $this->get('/admin-ti/dashboard')->assertRedirect('/login');
    }

    public function test_login_redirects_to_role_dashboard(): void
    {
        $this->post('/login', ['login' => 'pic.bima', 'password' => 'password'])
            ->assertRedirect(route('pic-branch.dashboard'));
    }

    public function test_super_admin_pages(): void
    {
        $this->actingAs($this->user('superadmin'));
        $u = User::first();
        $b = \App\Models\Branch::first();
        $v = \App\Models\Vendor::first();
        $m = \App\Models\DeviceModel::first();

        foreach ([
            route('super-admin.dashboard'),
            route('super-admin.users.index'), route('super-admin.users.create'), route('super-admin.users.edit', $u),
            route('super-admin.branches.index'), route('super-admin.branches.create'), route('super-admin.branches.edit', $b),
            route('super-admin.vendors.index'), route('super-admin.vendors.create'), route('super-admin.vendors.edit', $v),
            route('super-admin.device-models.index'), route('super-admin.device-models.create'), route('super-admin.device-models.edit', $m),
            route('super-admin.software-products.index'), route('super-admin.software-products.create'),
            route('super-admin.settings.index'), route('super-admin.activity-logs.index'),
            route('profile.edit'), route('notifications.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_ti_pages(): void
    {
        $this->actingAs($this->user('admin.ti'));
        $license = License::first();
        $device = Device::first();
        $renewal = LicenseRenewal::first();
        $approved = LicenseRenewal::create([
            'license_id' => $license->id, 'status' => RenewalStatus::APPROVED,
            'old_start_date' => $license->start_date, 'old_end_date' => $license->end_date,
            'new_start_date' => now(), 'new_end_date' => now()->addYear(),
        ]);

        foreach ([
            route('admin-ti.dashboard'),
            route('admin-ti.licenses.index'), route('admin-ti.licenses.index', ['status' => 'expired', 'q' => 'bima']),
            route('admin-ti.licenses.create'), route('admin-ti.licenses.show', $license), route('admin-ti.licenses.edit', $license),
            route('admin-ti.devices.index'), route('admin-ti.devices.create'), route('admin-ti.devices.show', $device), route('admin-ti.devices.edit', $device),
            route('admin-ti.renewals.index'), route('admin-ti.renewals.index', ['tab' => 'history']),
            route('admin-ti.renewals.show', $renewal), route('admin-ti.renewals.create', $license),
            route('admin-ti.renewals.complete-form', $approved),
            route('admin-ti.reports.index'), route('admin-ti.activity-logs.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('super-admin.dashboard'))->assertForbidden();
        $this->get(route('manajemen.approvals.index'))->assertForbidden();
    }

    public function test_manajemen_pages(): void
    {
        $this->actingAs($this->user('manajemen'));
        $license = License::first();
        $renewal = LicenseRenewal::first();
        $renewal->update(['status' => RenewalStatus::SUBMITTED]);

        foreach ([
            route('manajemen.dashboard'),
            route('manajemen.approvals.index'), route('manajemen.approvals.index', ['tab' => 'approved']),
            route('manajemen.approvals.show', $renewal),
            route('manajemen.licenses.index'), route('manajemen.licenses.show', $license),
            route('manajemen.reports.index'), route('manajemen.activity-logs.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('admin-ti.licenses.create'))->assertForbidden();
    }

    public function test_pic_branch_pages_are_scoped_to_own_branch(): void
    {
        $pic = $this->user('pic.bima');
        $this->actingAs($pic);
        $own = License::where('branch_id', $pic->branch_id)->first();
        $other = License::where('branch_id', '!=', $pic->branch_id)->first();

        foreach ([
            route('pic-branch.dashboard'),
            route('pic-branch.licenses.index'), route('pic-branch.licenses.show', $own),
            route('pic-branch.devices.index'),
            route('pic-branch.renewals.index'), route('pic-branch.renewals.create', $own),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('pic-branch.licenses.show', $other))->assertForbidden();
        $this->get(route('pic-branch.renewals.create', $other))->assertForbidden();
        $this->get(route('admin-ti.dashboard'))->assertForbidden();
    }
}
