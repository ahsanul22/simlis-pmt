<?php

namespace Tests\Feature;

use App\Enums\AssetType;
use App\Enums\AttachmentCategory;
use App\Enums\LicenseKeyFormat;
use App\Enums\LicenseType;
use App\Models\Attachment;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tidak semua lisensi punya key: none (perangkat), text, image (mis. foto key Microsoft Office). */
class LicenseKeyFormatTest extends TestCase
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

    private function officeLicense(): License
    {
        $office = SoftwareProduct::where('name', 'Microsoft Office')->firstOrFail();

        return License::create([
            'asset_type' => AssetType::SOFTWARE, 'license_type' => LicenseType::PERPETUAL,
            'vendor_id' => $office->vendor_id, 'branch_id' => 1, 'software_product_id' => $office->id,
            'name' => 'Office Test', 'key_format' => LicenseKeyFormat::IMAGE,
        ]);
    }

    public function test_excel_device_licenses_have_no_key(): void
    {
        $this->assertSame(0, License::where('key_format', '!=', LicenseKeyFormat::NONE->value)->count());
        $license = License::firstOrFail();
        $this->assertNull($license->keyIsRecorded());

        $this->actingAs($this->user('admin.ti'))
            ->get(route('admin-ti.licenses.show', $license))
            ->assertOk()->assertSee('Tidak memakai license key')->assertDontSee('Key belum diinput');
    }

    public function test_image_key_is_visible_only_to_key_viewers(): void
    {
        $license = $this->officeLicense();
        $this->assertFalse($license->keyIsRecorded());

        $this->actingAs($this->user('admin.ti'))
            ->get(route('admin-ti.licenses.show', $license))
            ->assertOk()->assertSee('Foto license key belum diunggah');

        Attachment::create([
            'attachable_type' => License::class, 'attachable_id' => $license->id,
            'category' => AttachmentCategory::LICENSE_KEY, 'path' => 'x/key.jpg', 'original_name' => 'key-office.jpg',
        ]);
        $this->assertTrue($license->fresh()->keyIsRecorded());

        $this->actingAs($this->user('admin.ti'))
            ->get(route('admin-ti.licenses.show', $license))
            ->assertOk()->assertSee('key-office.jpg');

        $this->actingAs($this->user('manajemen'))
            ->get(route('manajemen.licenses.show', $license))
            ->assertOk()->assertSee('Hanya untuk Admin TI')->assertDontSee('key-office.jpg');
    }

    public function test_license_form_offers_key_formats(): void
    {
        $this->actingAs($this->user('admin.ti'))
            ->get(route('admin-ti.licenses.create'))
            ->assertOk()->assertSee('Tanpa license key')->assertSee('Foto / gambar')->assertSee('key_images[]', false);
    }

    public function test_all_excel_data_listed_with_per_page(): void
    {
        $this->assertSame(21, License::whereHas('vendor', fn ($q) => $q->where('code', 'SANGFOR'))->count());

        $res = $this->actingAs($this->user('admin.ti'))->get(route('admin-ti.devices.index', ['per_page' => 100]))->assertOk();
        foreach (['WAADEE0015', 'WAAECB0325', 'WAZDBG0108', '021209007575', 'CN38L43019'] as $sn) {
            $res->assertSee($sn);
        }
    }
}
