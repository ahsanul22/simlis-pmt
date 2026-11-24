<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LicenseExpiryReminder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SuperAdminSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('username', 'superadmin')->firstOrFail());
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'warning' => 120,
            'critical' => 45,
            'reminder_days' => ['30', '120', '7', '30'],
            'mail' => [
                'subject' => 'Pengingat {aplikasi}: {jumlah} lisensi',
                'greeting' => 'Yth. {nama},',
                'intro' => "Mohon dicek.\r\nTerima kasih.",
                'closing' => "Hormat kami,\nTim TI Pelindo",
            ],
        ], $overrides);
    }

    public function test_settings_page_shows_template(): void
    {
        $this->get(route('super-admin.settings.index'))
            ->assertOk()
            ->assertSee('Template Email Pengingat')
            ->assertSee('{nama}');
    }

    public function test_update_settings(): void
    {
        $this->put(route('super-admin.settings.update'), $this->payload())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame(['warning' => 120, 'critical' => 45], Setting::expiryThresholds());
        $this->assertSame([120, 30, 7], Setting::reminderDays());
        $this->assertSame("Mohon dicek.\nTerima kasih.", Setting::reminderMail()['intro']);
        $this->assertDatabaseHas('activity_logs', ['event' => 'setting.updated']);
    }

    public function test_update_validation(): void
    {
        $payload = $this->payload(['critical' => 200, 'mail' => ['subject' => '']]); // critical harus < warning
        $payload['reminder_days'] = [];

        $this->put(route('super-admin.settings.update'), $payload)
            ->assertSessionHasErrors(['critical', 'reminder_days', 'mail.subject']);
    }

    public function test_reminder_email_uses_saved_template_and_escapes_html(): void
    {
        $this->put(route('super-admin.settings.update'), $this->payload([
            'mail' => ['intro' => 'Cek <script>alert(1)</script> sekarang'],
        ]));

        $user = User::where('username', 'manajemen')->firstOrFail();
        $notification = new LicenseExpiryReminder(License::with(['branch', 'vendor', 'device'])->limit(2)->get());
        $mail = $notification->toMail($user);
        $html = (string) $mail->render();

        $this->assertSame('Pengingat SIMLIS: 2 lisensi', $mail->subject);
        $this->assertStringContainsString('Yth. '.$user->name.',', $html);
        $this->assertStringContainsString('Tim TI Pelindo', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_send_test_mail(): void
    {
        Notification::fake();

        $this->post(route('super-admin.settings.test-mail'), ['test_email' => 'tes@example.com'])
            ->assertSessionHas('success');

        Notification::assertSentTo(new AnonymousNotifiable, LicenseExpiryReminder::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'tes@example.com');
    }
}
