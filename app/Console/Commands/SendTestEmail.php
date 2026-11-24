<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/** Cek cepat apakah konfigurasi MAIL_* (mis. Gmail SMTP) sudah benar. */
class SendTestEmail extends Command
{
    protected $signature = 'mail:test {email : Alamat tujuan}';

    protected $description = 'Kirim email percobaan untuk menguji konfigurasi SMTP';

    public function handle(): int
    {
        $email = $this->argument('email');
        $this->line('Mailer: '.config('mail.default').' · Host: '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port'));
        $this->line('Pengirim: '.config('mail.from.address'));

        try {
            Mail::raw(
                'Email ini menandakan konfigurasi email '.config('app.name').' sudah berjalan. Dikirim '.tgl_waktu(now()).'.',
                fn ($m) => $m->to($email)->subject('['.config('app.name').'] Tes koneksi email')
            );
        } catch (\Throwable $e) {
            $this->error('Gagal: '.$e->getMessage());
            $this->line('Untuk Gmail: pastikan MAIL_USERNAME = alamat Gmail, MAIL_PASSWORD = App Password 16 karakter (bukan password biasa), 2-Step Verification aktif.');

            return self::FAILURE;
        }

        $this->info("Terkirim ke {$email}. Cek inbox (atau folder Spam).");

        return self::SUCCESS;
    }
}
