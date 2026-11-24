# SIMLIS — Sistem Monitoring Lisensi TI

Aplikasi pemantauan masa berlaku lisensi **hardware & software** (Palo Alto, Sangfor, HPE Aruba, dst.) di seluruh lokasi (Kantor Pusat + branch). Dibangun ulang dari prototipe `public/referensi/*.html` (single-file, data hardcode, role palsu) + data Excel di folder yang sama.

Komunikasi dengan user: **Bahasa Indonesia santai**. User mengerjakan bertahap dan ingin mereview tiap tahap.

## Status pengerjaan

| Tahap | Status |
|---|---|
| 1. Role + database (migration, model, enum, seeder dari Excel) | ✅ selesai |
| 2. Frontend (layout, semua halaman per role, data GET asli) | ✅ selesai |
| 3. Backend (aksi tulis, validasi, notifikasi, scheduler, export) | ⏳ berikutnya |

### TODO tahap backend
- ✅ CRUD user Super Admin (`Requests/SuperAdmin/UserRequest` — contoh pola FormRequest + `ActivityLog::record()`; pengaman: tidak bisa ubah role/nonaktifkan/hapus diri sendiri, minimal 1 Super Admin aktif; hapus = soft delete, username/email tetap terpakai).
- Aksi tulis lain masih placeholder → cari `pendingBackend(` di `app/Http/Controllers`. Ganti dengan FormRequest (`app/Http/Requests/{Role}/...`) + logika simpan + `ActivityLog::record()`.
- Alur renewal: store (draft/submit sesuai `action`), cancel, approve/reject (Manajemen), complete (Admin TI: simpan periode lama ke `license_renewals`, update `licenses`, simpan lampiran). Satu lisensi maks. satu pengajuan terbuka (`RenewalStatus::openStatuses()`).
- Upload lampiran (`shared/attachments/_upload-modal` masih `action="#"`) → simpan ke storage, tabel `attachments` (polimorfik).
- Notifikasi: class Notification (database + mail), data wajib `title`, `message`, `url`, `icon` (dipakai header & halaman notifikasi). Contoh: `app/Notifications/LicenseExpiryReminder.php` + view `resources/views/mail/*`. Notifikasi alur renewal (diajukan/disetujui/ditolak/selesai) belum ada.
- ✅ Email via Gmail SMTP (`.env` MAIL_* + App Password), `php artisan mail:test {email}`.
- ✅ Pengaturan Super Admin tersimpan: ambang status, jadwal reminder, template email (`Setting::reminderMail()`, placeholder `{nama}` `{jumlah}` `{judul}` `{aplikasi}`), tombol kirim email contoh.
- ✅ Reminder harian `licenses:send-reminders` (`--dry-run`, `--to=`), terjadwal di `Console\Kernel` jam `REMINDER_TIME`; butuh `php artisan schedule:work` / Task Scheduler di server.
- Export laporan Excel/PDF (`reports.export`), update profil & password, settings update.
- Ganti `authorizeBranch()` di base Controller dengan Policy bila logika otorisasi bertambah.

## Stack & perintah

- Laravel 10 (PHP 8.2), MySQL/MariaDB (XAMPP, `.env` → `DB_DATABASE`), Blade + Tailwind 3 + Alpine.js 3 + Chart.js 4 via Vite. Font Inter dibundel (offline-friendly). **Tidak ada 3D** (sengaja dibuang dari referensi).
- `php artisan migrate` — schema **+ data asli** (pengaturan, master lokasi/vendor/model, 55 perangkat & lisensi dari Excel) lewat migration `2026_11_23_000012_import_data_referensi_excel` (query builder, bukan Eloquent).
- `php artisan db:seed` — hanya akun demo (password `password`: `superadmin`, `admin.ti`, `manajemen`, `pic.bima`, `pic.makassar`). `migrate:fresh --seed` = reset total untuk development.
- `npm run dev` (development) / `npm run build` (wajib setelah mengubah class Tailwind/JS jika tidak menjalankan dev server).
- `php artisan test` — test memakai SQLite in-memory (lihat `phpunit.xml`), tidak menyentuh MySQL. `tests/Feature/PageAccessTest.php` membuka semua halaman per role + cek 403/scope branch. **Tambahkan URL baru ke test ini setiap menambah halaman.**

## Role (sumber kebenaran: `app/Enums/UserRole.php`)

| Role | Prefix URL / nama route / folder view / namespace controller |
|---|---|
| Super Admin — user, master data, pengaturan, audit | `super-admin` / `super-admin.*` / `views/super-admin` / `Controllers\SuperAdmin` |
| Admin TI — CRUD perangkat & lisensi, selesaikan perpanjangan | `admin-ti` / `admin-ti.*` / `views/admin-ti` / `Controllers\AdminTi` |
| Manajemen — lihat semua, setujui/tolak perpanjangan | `manajemen` / `manajemen.*` / `views/manajemen` / `Controllers\Manajemen` |
| PIC Branch — hanya branch sendiri, ajukan perpanjangan | `pic-branch` / `pic-branch.*` / `views/pic-branch` / `Controllers\PicBranch` |

- Hak akses granular: `UserRole::permissions()` → `$user->hasPermission('license.manage')`. Dipakai di view untuk menampilkan/menyembunyikan aksi.
- Scope data PIC Branch: scope `visibleTo($user)` (trait `Models\Concerns\ScopedByBranch`, `LicenseRenewal::scopeVisibleTo`) + `authorizeBranch($model)` di controller untuk halaman detail.
- Route middleware: `role:admin_ti` (`EnsureUserHasRole`). Route tiap role di `routes/roles/{prefix}.php`, di-include dari `routes/web.php`.

## Struktur & konvensi

```
app/Enums/                 status & tipe (string-backed). Kolom DB = string, cast ke enum di model.
                           Enum punya label() & badge() (kelas Tailwind) → <x-badge :enum="..."/>
app/Http/Controllers/{Role}/     controller tipis per role
app/Http/Controllers/Concerns/   query bersama lintas role: ListsLicenses, ListsRenewals, BuildsReport, ListsActivityLogs
app/Services/LicenseStatsService angka dashboard/laporan & konfigurasi chart (mengikuti scope user)
app/View/Composers/LayoutComposer data header/sidebar: $authUser, notifikasi, $menuBadges
app/Support/helpers.php    tgl(), tgl_waktu(), rupiah(), sisa_hari(), role_route()
resources/views/
  layouts/app|auth         kerangka halaman; @yield('title'), @yield('content'), @stack('scripts')
  includes/                head, header, sidebar, footer, scripts, flash, pagination, menus/{role}
  components/              x-icon, x-page-header, x-stat-card, x-badge, x-modal, x-delete-button,
                           x-empty-state, x-period-bar, x-nav-link, x-form.{input,select,textarea,toggle}
  shared/                  partial lintas role (prefix "_"): licenses, renewals, devices, dashboard,
                           reports, activity-logs, attachments
  {role-prefix}/           halaman per role: dashboard, {resource}/index|show|form|create
```

- Halaman baru: route di `routes/roles/{prefix}.php` → controller di namespace role → view di `views/{prefix}/...` yang `@extends('layouts.app')` dan memakai `x-page-header`. Menu di `includes/menus/{prefix}.blade.php`.
- View yang dipakai >1 role taruh di `views/shared/`, link di dalamnya pakai `role_route('licenses.show', $x)` agar otomatis ke route role yang login.
- Form create & edit memakai satu file `form.blade.php` (`$model->exists` untuk bedakan).
- Ikon: tambah path di `components/icon.blade.php` (gaya Heroicons outline).
- Chart: `<canvas x-data="chart(@js($config))">` (komponen Alpine di `resources/js/app.js`), config dari `LicenseStatsService`.
- Konfirmasi hapus pakai `x-delete-button` / `x-modal` (jangan `confirm()` browser).
- Dark mode: class `dark` di `<html>`, toggle `$store.theme`. Setiap warna baru wajib punya varian `dark:`.

## Aturan domain penting

- Status masa berlaku **tidak disimpan**, dihitung dari `end_date`: `$license->days_remaining`, `$license->expiry_status`, scope `whereExpiryStatus()`. Ambang dari tabel `settings` (`Setting::expiryThresholds()`, default warning 90 / critical 30).
- `devices` (unit fisik) terpisah dari `licenses` — satu perangkat bisa punya beberapa lisensi (subscription + warranty). Lisensi software memakai `software_product_id` (tanpa device).
- **Tidak semua lisensi punya key** → `licenses.key_format` (`App\Enums\LicenseKeyFormat`): `none` (lisensi perangkat terikat SN — semua data Excel), `text` (`license_key`, cast `encrypted` + `$hidden`), `image` (foto key, mis. Microsoft Office → lampiran kategori `AttachmentCategory::LICENSE_KEY`, relasi `License::keyImages()`). `License::keyIsRecorded()` → null jika memang tanpa key. Field form: `key_format`, `license_key`, `key_images[]`; form selesai renewal: `new_license_key` / `key_images[]` sesuai format.
- Key teks & foto key hanya untuk role dengan `license.view_key`. Lampiran sensitif (`AttachmentCategory::isSensitive()`) disembunyikan di `shared/attachments/_list` dan pilihan kategori upload memakai `AttachmentCategory::optionsFor($user)`. Saat backend: simpan foto key di disk private & download lewat route yang cek izin.
- Riwayat perpanjangan = baris `license_renewals` berstatus `completed` (snapshot `old_*` → `new_*`).
- Kode otomatis: `LIC-YYYY-00001` (License), `RNW-YYYY-00001` (LicenseRenewal) di event `creating`.
- Data Excel: tanpa license key (key_format none), Sangfor tanpa start date. Jangan mengarang data. Data asli baru → buat migration data baru (jangan ditaruh di seeder).

## Gotcha

- Jangan pakai `use ...;` di dalam `@php` Blade (Laravel 10) — tulis nama class lengkap `\App\Enums\...`.
- Komponen form sudah meng-handle `id` (pakai `$attributes->except('id')`); untuk dua field bernama sama di satu halaman, beri `id` berbeda.
- Locale `id`, timezone `Asia/Jakarta` (config/app.php). Format tanggal via `tgl()` (Carbon `translatedFormat`).
- Windows + Git Bash: hati-hati escape backslash namespace saat edit file via sed; lebih aman pakai tool Edit.
- Dokumen desain lengkap (matriks role, ERD, alur): `docs/DESAIN-ROLE-DAN-DATABASE.md`.
