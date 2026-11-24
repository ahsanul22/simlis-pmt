# Cara Menjalankan & Menguji SIMLIS

Catatan singkat untuk membuka project ini di komputer lokal (Windows + XAMPP).

## Pertama kali (sekali saja)

```bash
composer install
npm install
copy .env.example .env      # lalu sesuaikan DB_DATABASE & MAIL_* (lihat bagian Email)
php artisan key:generate
php artisan migrate --seed  # buat tabel + data Excel + akun demo
npm run build
```

> Pastikan **Apache & MySQL di XAMPP sudah Start** dan database (nama sesuai `DB_DATABASE`) sudah dibuat di phpMyAdmin.

## Setiap kali mau membuka project

Buka 2 terminal di folder project:

| Terminal | Perintah | Fungsi |
|---|---|---|
| 1 | `php artisan serve` | Menjalankan website → buka http://127.0.0.1:8000 |
| 2 | `npm run dev` | Tampilan otomatis ter-update saat mengedit (opsional, hanya saat ngoding) |

Kalau ingin reminder email terkirim otomatis, tambah terminal ke-3: `php artisan schedule:work` (biarkan terbuka).

## Akun demo

Password semua akun: `password`

| Username | Role |
|---|---|
| `superadmin` | Super Admin |
| `admin.ti` | Admin TI |
| `manajemen` | Manajemen |
| `pic.bima` / `pic.makassar` | PIC Branch |

## Email (Gmail)

1. Isi di `.env`: `MAIL_USERNAME` (alamat Gmail pengirim) dan `MAIL_PASSWORD` (App Password 16 huruf, **tanpa spasi**).
2. `php artisan config:clear`
3. Tes: `php artisan mail:test emailkamu@gmail.com`

- Email di `.env` = **pengirim**. Penerima = email masing-masing user (ubah di menu **Super Admin → Manajemen User**).
- Isi email & jadwal pengingat bisa diubah di **Super Admin → Pengaturan** (ada tombol *Kirim Email Contoh*).

### Mengganti email pengirim

Pengirim **hanya** bisa diganti lewat `.env` (bukan dari website), karena butuh App Password dari akun baru.

1. Di akun Gmail yang baru: aktifkan **2-Step Verification**, lalu buat App Password di [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords).
2. Ubah 2 baris di `.env`:
   ```
   MAIL_USERNAME=emailbaru@gmail.com
   MAIL_PASSWORD=abcdefghijklmnop
   ```
   (`MAIL_FROM_ADDRESS` otomatis ikut. Nama pengirim bisa diubah lewat `MAIL_FROM_NAME="SIMLIS Pelindo"`.)
3. Jalankan `php artisan config:clear`, lalu restart `php artisan serve` / `schedule:work` jika sedang jalan.
4. Tes: **Pengaturan → Uji Kirim Email**, atau `php artisan mail:test emailkamu@gmail.com`.
5. Hapus App Password di akun lama (sudah tidak dipakai).

> ⚠️ **Jangan pakai akun Google pribadi/utama sebagai pengirim.** App Password = akses ke Gmail tersebut; kalau `.env` bocor, orang lain bisa membaca & mengirim email atas nama Anda. Pakai akun khusus (mis. `simlis.pelindo@gmail.com`) atau email resmi kantor (mis. `noreply@pelindo.co.id`, minta ke tim TI).

## Menguji

| Perintah | Untuk apa |
|---|---|
| `php artisan test` | Jalankan semua test otomatis (tidak menyentuh database MySQL) |
| `php artisan licenses:send-reminders --dry-run` | Lihat lisensi & penerima reminder hari ini, tanpa mengirim |
| `php artisan licenses:send-reminders --to=emailkamu@gmail.com` | Kirim contoh reminder ke satu email saja |
| `php artisan licenses:send-reminders` | Kirim reminder sungguhan (yang otomatis dijalankan tiap pagi) |

## Kalau ada masalah

| Gejala | Solusi |
|---|---|
| Tampilan berantakan / tidak ada style | `npm run build` (atau jalankan `npm run dev`) |
| Ubah `.env` tapi tidak berefek | `php artisan config:clear` |
| `The environment file is invalid` | Ada spasi di nilai `.env` (mis. App Password) → hapus spasinya |
| Email tidak terkirim | Cek `storage/logs/laravel.log`, pastikan App Password benar |
| Ingin reset database ke awal | `php artisan migrate:fresh --seed` (**semua data dihapus**) |
