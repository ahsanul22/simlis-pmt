<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\License;
use App\Models\LicenseReminderLog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LicenseExpiryReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Dijalankan harian oleh scheduler (lihat App\Console\Kernel).
 *
 * Untuk tiap lisensi aktif, ambang yang berlaku = ambang terkecil di Setting::reminderDays()
 * yang >= sisa hari. Bila ambang itu belum tercatat di license_reminder_logs
 * (unik per license + end_date + threshold + channel), lisensi masuk reminder hari ini.
 * Jadi kalau scheduler sempat mati beberapa hari, ambang yang terlewat tetap terkirim sekali.
 *
 * Penerima: Admin TI & Manajemen (semua lisensi), PIC Branch (lisensi di branch-nya).
 */
class SendLicenseReminders extends Command
{
    protected $signature = 'licenses:send-reminders
                            {--dry-run : Tampilkan saja lisensi & penerima, tanpa mengirim}
                            {--to= : Uji coba — kirim semua reminder ke satu email ini saja (tidak dicatat sebagai terkirim)}';

    protected $description = 'Kirim email & notifikasi pengingat lisensi yang mendekati masa habis';

    private const CHANNEL = 'mail';

    public function handle(): int
    {
        $due = $this->dueLicenses();

        if ($due->isEmpty()) {
            $this->info('Tidak ada lisensi yang perlu diingatkan hari ini.');

            return self::SUCCESS;
        }

        $this->table(
            ['Kode', 'Lisensi', 'Lokasi', 'Habis', 'Sisa', 'Ambang'],
            $due->map(fn (array $d) => [
                $d['license']->code, $d['license']->name, $d['license']->branch?->name,
                tgl($d['license']->end_date), $d['license']->days_remaining.' hari', 'H-'.$d['threshold'],
            ])
        );

        $licenses = $due->pluck('license');

        if ($to = $this->option('to')) {
            if ($this->option('dry-run')) {
                return self::SUCCESS;
            }
            Notification::route('mail', $to)->notify(new LicenseExpiryReminder($licenses));
            $this->info("Email uji coba ({$licenses->count()} lisensi) dikirim ke {$to}. Tidak dicatat di log reminder.");

            return self::SUCCESS;
        }

        $sentPerLicense = [];
        $failed = 0;

        foreach ($this->recipients() as $user) {
            $own = $user->isBranchScoped()
                ? $licenses->where('branch_id', $user->branch_id)->values()
                : $licenses;

            if ($own->isEmpty()) {
                continue;
            }

            $this->line("→ {$user->name} <{$user->email}> ({$user->role->label()}): {$own->count()} lisensi");

            if ($this->option('dry-run')) {
                continue;
            }

            try {
                $user->notify(new LicenseExpiryReminder($own));
                foreach ($own as $l) {
                    $sentPerLicense[$l->id] = ($sentPerLicense[$l->id] ?? 0) + 1;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
                $this->error("  Gagal kirim ke {$user->email}: {$e->getMessage()}");
            }
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run — tidak ada yang dikirim.');

            return self::SUCCESS;
        }

        foreach ($due as $d) {
            if (! isset($sentPerLicense[$d['license']->id])) {
                continue; // belum terkirim ke siapa pun → coba lagi besok
            }
            LicenseReminderLog::firstOrCreate(
                [
                    'license_id' => $d['license']->id,
                    'end_date' => $d['license']->end_date->toDateString(),
                    'threshold_days' => $d['threshold'],
                    'channel' => self::CHANNEL,
                ],
                ['recipients_count' => $sentPerLicense[$d['license']->id], 'sent_at' => now()]
            );
        }

        $this->info(count($sentPerLicense).' lisensi diingatkan.'.($failed ? " {$failed} penerima gagal." : ''));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @return Collection<int, array{license: License, threshold: int}> */
    private function dueLicenses(): Collection
    {
        $thresholds = collect(Setting::reminderDays())->filter(fn ($d) => $d >= 0)->unique()->sort()->values();

        if ($thresholds->isEmpty()) {
            return collect();
        }

        $licenses = License::active()
            ->with(['branch', 'vendor', 'device'])
            ->expiringWithin($thresholds->max())
            ->orderBy('end_date')
            ->get();

        $sent = LicenseReminderLog::where('channel', self::CHANNEL)
            ->whereIn('license_id', $licenses->pluck('id'))
            ->get()
            ->map(fn ($log) => $log->license_id.'|'.$log->end_date->toDateString().'|'.$log->threshold_days)
            ->flip();

        return $licenses->map(function (License $license) use ($thresholds, $sent) {
            $threshold = $thresholds->first(fn ($d) => $license->days_remaining <= $d);
            $key = $license->id.'|'.$license->end_date->toDateString().'|'.$threshold;

            return $threshold === null || $sent->has($key) ? null : ['license' => $license, 'threshold' => $threshold];
        })->filter()->values();
    }

    /** @return Collection<int, User> */
    private function recipients(): Collection
    {
        return User::active()
            ->role(UserRole::ADMIN_TI, UserRole::MANAJEMEN, UserRole::PIC_BRANCH)
            ->whereNotNull('email')
            ->get()
            ->reject(fn (User $u) => $u->isBranchScoped() && ! $u->branch_id);
    }
}
