<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\SettingRequest;
use App\Models\ActivityLog;
use App\Models\License;
use App\Models\Setting;
use App\Notifications\LicenseExpiryReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('super-admin.settings.index', [
            'thresholds' => Setting::expiryThresholds(),
            'reminderDays' => Setting::reminderDays(),
            'mail' => Setting::reminderMail(),
            'mailDefaults' => Setting::REMINDER_MAIL_DEFAULTS,
            'placeholders' => Setting::REMINDER_MAIL_PLACEHOLDERS,
            'mailConfigured' => filled(config('mail.mailers.smtp.username')) || config('mail.default') !== 'smtp',
            'mailFrom' => config('mail.from.address'),
            'sampleLicenses' => $this->sampleLicenses(),
        ]);
    }

    /** 3 lisensi aktif terdekat masa habisnya — untuk pratinjau & email contoh. */
    private function sampleLicenses(): Collection
    {
        return License::active()->with(['branch', 'vendor', 'device'])
            ->whereNotNull('end_date')->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('end_date')->limit(3)->get();
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        $before = $this->snapshot();

        Setting::put(Setting::EXPIRY_THRESHOLDS, [
            'warning' => (int) $request->warning,
            'critical' => (int) $request->critical,
        ]);
        Setting::put(Setting::REMINDER_DAYS, $request->validated('reminder_days'));
        Setting::put(Setting::REMINDER_MAIL, $request->validated('mail'));

        $after = $this->snapshot();
        $changed = array_keys(array_filter($after, fn ($v, $k) => $v !== $before[$k], ARRAY_FILTER_USE_BOTH));

        if (! $changed) {
            return back()->with('info', 'Tidak ada perubahan pengaturan.');
        }

        ActivityLog::record('setting.updated', null, 'Mengubah pengaturan: '.implode(', ', $changed), [
            'old' => array_intersect_key($before, array_flip($changed)),
            'new' => array_intersect_key($after, array_flip($changed)),
        ]);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    /** Kirim contoh email reminder (template tersimpan) ke alamat yang diisi, default email sendiri. */
    public function testMail(Request $request): RedirectResponse
    {
        $data = $request->validate(['test_email' => ['required', 'email:rfc']], [], ['test_email' => 'email tujuan']);

        $sample = $this->sampleLicenses();

        if ($sample->isEmpty()) {
            return back()->with('warning', 'Belum ada lisensi aktif untuk dijadikan contoh.');
        }

        try {
            Notification::route('mail', $data['test_email'])->notify(new LicenseExpiryReminder($sample));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Email gagal dikirim: '.$e->getMessage().' — periksa pengaturan MAIL_* di file .env.');
        }

        return back()->with('success', "Email contoh terkirim ke {$data['test_email']}. Cek inbox atau folder Spam.");
    }

    private function snapshot(): array
    {
        return [
            'ambang status' => Setting::expiryThresholds(),
            'jadwal pengingat' => Setting::reminderDays(),
            'template email' => Setting::reminderMail(),
        ];
    }
}
