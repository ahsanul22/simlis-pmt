<?php

namespace App\Notifications;

use App\Models\License;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Ringkasan (digest) lisensi yang mencapai ambang reminder hari ini.
 * Satu notifikasi per penerima berisi semua lisensi yang relevan untuknya,
 * supaya inbox tidak dibanjiri satu email per lisensi.
 */
class LicenseExpiryReminder extends Notification
{
    use Queueable;

    /** @param Collection<int, License> $licenses sudah di-eager load: branch, vendor, device */
    public function __construct(public Collection $licenses)
    {
    }

    public function via(object $notifiable): array
    {
        // Penerima uji coba (--to) bukan User → hanya email.
        return $notifiable instanceof User ? ['database', 'mail'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $licenses = $this->licenses->sortBy('end_date')->values();
        $text = $this->template($notifiable);

        return (new MailMessage)
            ->subject($text['subject'])
            ->markdown('mail.license-expiry-reminder', [
                'text' => $text,
                'licenses' => $licenses,
                'url' => $this->url($notifiable),
                'linkFor' => fn (License $l) => route($this->prefix($notifiable).'.licenses.show', $l),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $soonest = $this->licenses->sortBy('end_date')->first();

        return [
            'title' => $this->title(),
            'message' => $this->licenses->count() === 1
                ? $soonest->name.' ('.($soonest->branch?->name ?? '-').') habis '.tgl($soonest->end_date).'.'
                : 'Terdekat: '.$soonest->name.' ('.($soonest->branch?->name ?? '-').') habis '.tgl($soonest->end_date).'.',
            'url' => $this->url($notifiable),
            'icon' => 'clock',
            'license_ids' => $this->licenses->pluck('id')->all(),
        ];
    }

    /** Template dari Pengaturan (Super Admin) dengan placeholder terisi. */
    public function template(object $notifiable): array
    {
        $values = [
            '{nama}' => $notifiable instanceof User ? $notifiable->name : 'Bapak/Ibu',
            '{jumlah}' => (string) $this->licenses->count(),
            '{judul}' => $this->title(),
            '{aplikasi}' => config('app.name'),
        ];

        return array_map(fn (string $t) => trim(strtr($t, $values)), Setting::reminderMail());
    }

    private function title(): string
    {
        $count = $this->licenses->count();

        if ($count === 1) {
            $days = $this->licenses->first()->days_remaining;

            return $days === 0
                ? 'Lisensi habis masa berlakunya hari ini'
                : "Lisensi akan habis dalam {$days} hari";
        }

        return "{$count} lisensi akan segera habis masa berlakunya";
    }

    private function url(object $notifiable): string
    {
        $prefix = $this->prefix($notifiable);

        return $this->licenses->count() === 1
            ? route($prefix.'.licenses.show', $this->licenses->first())
            : route($prefix.'.licenses.index');
    }

    /** Prefix route role penerima. Super Admin & penerima uji coba diarahkan ke halaman Admin TI. */
    private function prefix(object $notifiable): string
    {
        $prefix = $notifiable instanceof User ? $notifiable->role->routePrefix() : 'admin-ti';

        return in_array($prefix, ['admin-ti', 'manajemen', 'pic-branch'], true) ? $prefix : 'admin-ti';
    }
}
