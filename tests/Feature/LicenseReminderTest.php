<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseReminderLog;
use App\Models\User;
use App\Notifications\LicenseExpiryReminder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LicenseReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Notification::fake();
    }

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    public function test_sends_reminder_once_per_threshold_and_respects_branch_scope(): void
    {
        $pic = $this->user('pic.bima');
        $other = License::where('branch_id', '!=', $pic->branch_id)->firstOrFail();
        $other->update(['end_date' => now()->addDays(10), 'is_terminated' => false]);

        $this->artisan('licenses:send-reminders')->assertSuccessful();

        // Admin TI & Manajemen menerima lisensi branch lain, PIC Bima tidak.
        foreach (['admin.ti', 'manajemen'] as $username) {
            Notification::assertSentTo($this->user($username), LicenseExpiryReminder::class,
                fn ($n, $channels) => $n->licenses->contains('id', $other->id) && in_array('mail', $channels));
        }
        Notification::assertNotSentTo($pic, LicenseExpiryReminder::class,
            fn ($n) => $n->licenses->contains('id', $other->id));
        Notification::assertNotSentTo($this->user('superadmin'), LicenseExpiryReminder::class);

        $this->assertDatabaseHas('license_reminder_logs', ['license_id' => $other->id, 'threshold_days' => 14, 'channel' => 'mail']);

        // Jalan lagi di hari yang sama → tidak ada yang dikirim ulang.
        $count = LicenseReminderLog::count();
        Notification::fake();
        $this->artisan('licenses:send-reminders')->assertSuccessful();
        Notification::assertNothingSent();
        $this->assertSame($count, LicenseReminderLog::count());
    }

    public function test_dry_run_and_test_recipient_do_not_write_logs(): void
    {
        License::firstOrFail()->update(['end_date' => now()->addDays(5), 'is_terminated' => false]);

        $this->artisan('licenses:send-reminders --dry-run')->assertSuccessful();
        Notification::assertNothingSent();

        $this->artisan('licenses:send-reminders --to=tes@example.com')->assertSuccessful();
        Notification::assertSentOnDemand(LicenseExpiryReminder::class);

        $this->assertSame(0, LicenseReminderLog::count());
    }

    public function test_reminder_email_renders(): void
    {
        $licenses = License::with(['branch', 'vendor', 'device'])->limit(2)->get();
        $html = (string) (new LicenseExpiryReminder($licenses))->toMail($this->user('manajemen'))->render();

        $this->assertStringContainsString($licenses->first()->code, $html);
        $this->assertStringContainsString(route('manajemen.licenses.show', $licenses->first()), $html);
    }
}
