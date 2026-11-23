<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data awal (bukan demo) agar cukup `php artisan migrate` di server baru:
 * - pengaturan ambang status & reminder
 * - master lokasi, vendor, model perangkat
 * - perangkat + lisensi dari "public/referensi/License activation date perangkat Palo Alto, Sangfor dan Aruba.xlsx"
 *
 * Catatan data Excel:
 * - License key tidak ada di Excel; lisensi perangkat ini terikat ke SN -> key_format = none.
 * - Sheet SANGFOR tidak punya START DATE -> start_date null (tidak dikarang).
 * - Sheet ARUBA tabel atas (24x7 SVC, s.d. 31 Okt 2026) = periode lama -> riwayat perpanjangan berstatus completed.
 *
 * Akun demo TIDAK ada di sini (lihat database/seeders/UserSeeder.php).
 * Memakai query builder (bukan Eloquent) supaya migration tetap jalan walau model berubah di kemudian hari.
 */
return new class extends Migration
{
    private const BRANCHES = [
        'KP' => 'Kantor Pusat',
        'GRK' => 'Garongkong',
        'BDS' => 'Badas',
        'SBG' => 'Sibolga',
        'LGS' => 'Langsa',
        'MLB' => 'Meulaboh',
        'TBK' => 'Tanjung Balai Karimun',
        'TJE' => 'Tanjung Emas',
        'PLP' => 'Pelindo Place',
        'MLH' => 'Malahayati',
        'LSW' => 'Lhokseumawe',
        'PRP' => 'Parepare',
        'GRS' => 'Gresik',
        'LMB' => 'Lembar',
        'TPI' => 'Tanjung Pinang',
        'JNR' => 'Janirah',
        'BPN' => 'Balikpapan',
        'BGD' => 'Bagendang',
        'MKS' => 'Makassar',
        'TJW' => 'Tanjung Wangi',
        'BMH' => 'Bumiharjo',
        'DUM' => 'Dumai',
        'TRS' => 'Trisakti',
        'TJI' => 'Tanjung Intan',
        'BIM' => 'Bima',
    ];

    private const VENDORS = [
        'PAN' => ['Palo Alto Networks', 'https://www.paloaltonetworks.com'],
        'SANGFOR' => ['Sangfor Technologies', 'https://www.sangfor.com'],
        'HPE' => ['HPE Aruba Networking', 'https://www.arubanetworks.com'],
        'MSFT' => ['Microsoft', 'https://www.microsoft.com'],
    ];

    // [vendor, model, kategori, deskripsi]
    private const MODELS = [
        ['PAN', 'PA-440', 'firewall', 'Next-Generation Firewall untuk branch'],
        ['PAN', 'PA-3410', 'firewall', 'Next-Generation Firewall untuk kantor pusat'],
        ['SANGFOR', 'IAG 50', 'web_gateway', 'Internet Access Gateway'],
        ['SANGFOR', 'IAG 51', 'web_gateway', 'Internet Access Gateway'],
        ['SANGFOR', 'IAG 52', 'web_gateway', 'Internet Access Gateway'],
        ['HPE', 'HPE 5710', 'switch', 'Aruba CX 5710 Switch'],
        ['HPE', 'HPE 5520', 'switch', 'Aruba CX 5520 Switch'],
    ];

    // Produk software (master saja, lisensinya diinput Admin TI). Key Office biasanya berupa foto.
    private const SOFTWARE = [
        ['MSFT', 'Microsoft Office', null, 'Office', 'Lisensi Office; license key umumnya berupa foto/kartu'],
    ];

    // [branch, model, serial_number, start_date, end_date] — sheet PALO ALTO
    private const PALO_ALTO = [
        ['GRK', 'PA-440', '021209007725', '2026-02-04', '2027-10-10'],
        ['BDS', 'PA-440', '021209007564', '2026-02-04', '2027-10-10'],
        ['SBG', 'PA-440', '021209007568', '2026-02-02', '2027-10-10'],
        ['LGS', 'PA-440', '021209007723', '2026-02-04', '2027-10-10'],
        ['MLB', 'PA-440', '021209007698', '2026-02-04', '2027-10-10'],
        ['TBK', 'PA-440', '021209007709', '2026-02-04', '2027-10-10'],
        ['TJE', 'PA-440', '021209007633', '2026-02-04', '2027-10-10'],
        ['PLP', 'PA-440', '021209007572', '2026-02-03', '2027-10-10'],
        ['MLH', 'PA-440', '021201170755', '2026-02-04', '2027-10-10'],
        ['LSW', 'PA-440', '021209007569', '2026-02-04', '2027-10-10'],
        ['PRP', 'PA-440', '021209007552', '2026-02-04', '2027-10-10'],
        ['GRS', 'PA-440', '021209007691', '2026-02-04', '2027-10-10'],
        ['LMB', 'PA-440', '021209007573', '2026-02-04', '2027-10-10'],
        ['TPI', 'PA-440', '021201175335', '2026-11-04', '2027-10-10'],
        ['JNR', 'PA-440', '021201121147', '2026-02-20', '2027-10-10'],
        ['BPN', 'PA-440', '021201121509', '2026-02-20', '2027-10-10'],
        ['BGD', 'PA-440', '021201121163', '2026-02-20', '2027-10-10'],
        ['MKS', 'PA-440', '021201121146', '2026-02-20', '2027-10-10'],
        ['TJW', 'PA-440', '021201121236', '2026-02-20', '2027-10-10'],
        ['BMH', 'PA-440', '021201121148', '2026-02-20', '2027-10-10'],
        ['DUM', 'PA-440', '021201121462', '2026-02-20', '2027-10-10'],
        ['TRS', 'PA-440', '021201121467', '2026-02-20', '2027-10-10'],
        ['TJI', 'PA-440', '021201121243', '2026-02-20', '2027-10-10'],
        ['BIM', 'PA-440', '021209007575', '2026-02-15', '2027-02-04'],
        ['KP', 'PA-3410', '024101012277', '2026-02-20', '2027-10-10'],
    ];

    // sheet SANGFOR (tanpa start date)
    private const SANGFOR = [
        ['KP', 'IAG 52', 'WAADEE0015', null, '2027-10-10'],
        ['DUM', 'IAG 51', 'WAZDEF0001', null, '2027-10-10'],
        ['TRS', 'IAG 50', 'WAZDEF0006', null, '2027-10-10'],
        ['MKS', 'IAG 51', 'WAZDEF0002', null, '2027-10-10'],
        ['BPN', 'IAG 51', 'WAZDEF0003', null, '2027-10-10'],
        ['BMH', 'IAG 50', 'WAZDEF0004', null, '2027-10-10'],
        ['JNR', 'IAG 51', 'WAZDEEF0007', null, '2027-10-10'],
        ['MLH', 'IAG 51', 'WAADEE0001', null, '2027-10-10'],
        ['LSW', 'IAG 51', 'WAAEBB0012', null, '2027-10-10'],
        ['PRP', 'IAG 51', 'WAAEBB0007', null, '2027-10-10'],
        ['GRK', 'IAG 51', 'WAAEBB0003', null, '2027-10-10'],
        ['LMB', 'IAG 51', 'WAAEBB0004', null, '2027-10-10'],
        ['SBG', 'IAG 51', 'WAAECB0327', null, '2027-10-10'],
        ['PLP', 'IAG 51', 'WAAECB0324', null, '2027-10-10'],
        ['TJE', 'IAG 51', 'WAAECB0332', null, '2027-10-10'],
        ['GRS', 'IAG 51', 'WAAECB0328', null, '2027-10-10'],
        ['BDS', 'IAG 51', 'WAAECB0329', null, '2027-10-10'],
        ['TJW', 'IAG 51', 'WAZDBG0083', null, '2027-10-10'],
        ['TJI', 'IAG 51', 'WAZDBG0084', null, '2027-10-10'],
        ['BGD', 'IAG 50', 'WAZDBG0108', null, '2027-10-10'],
        ['BIM', 'IAG 51', 'WAAECB0325', null, '2026-10-10'],
    ];

    // sheet ARUBA, tabel bawah (periode aktif)
    private const ARUBA = [
        ['KP', 'HPE 5710', 'CN39KFV055', '2026-10-01', '2027-10-31'],
        ['DUM', 'HPE 5710', 'CN39KFV05K', '2026-10-01', '2027-10-31'],
        ['TJE', 'HPE 5710', 'CN39KFV03P', '2026-10-01', '2027-10-31'],
        ['MKS', 'HPE 5520', 'CN38L4301C', '2026-10-01', '2027-10-31'],
        ['LMB', 'HPE 5520', 'CN3BL4002P', '2026-10-01', '2027-10-31'],
        ['TRS', 'HPE 5520', 'CN26L4021X', '2026-10-01', '2027-10-31'],
        ['MLH', 'HPE 5520', 'CN3BL40097', '2026-10-01', '2027-10-31'],
        ['TJI', 'HPE 5520', 'CN38L4301N', '2026-10-01', '2027-10-31'],
        ['TJW', 'HPE 5520', 'CN38L43019', '2026-10-01', '2027-10-31'],
    ];

    // sheet ARUBA, tabel atas (periode lama 15 Des 2025 – 31 Okt 2026, 24x7 SVC)
    private const ARUBA_HISTORY_SN = ['CN39KFV055', 'CN39KFV05K', 'CN39KFV03P', 'CN38L4301C', 'CN3BL4002P', 'CN26L4021X', 'CN3BL40097'];

    public function up(): void
    {
        $now = now();
        $ts = ['created_at' => $now, 'updated_at' => $now];

        DB::transaction(function () use ($now, $ts) {
            // Pengaturan
            DB::table('settings')->insertOrIgnore([
                ['key' => 'expiry_thresholds', 'value' => json_encode(['warning' => 90, 'critical' => 30]),
                    'description' => 'Batas hari status: <= warning = "Akan Habis", <= critical = "Kritis"'] + $ts,
                ['key' => 'reminder_days', 'value' => json_encode([90, 60, 30, 14, 7, 1]),
                    'description' => 'Hari sebelum end date saat reminder email/in-app dikirim'] + $ts,
            ]);

            // Master
            foreach (self::BRANCHES as $code => $name) {
                DB::table('branches')->insertOrIgnore([
                    'code' => $code, 'name' => $name, 'type' => $code === 'KP' ? 'kantor_pusat' : 'branch', 'is_active' => true,
                ] + $ts);
            }
            foreach (self::VENDORS as $code => [$name, $website]) {
                DB::table('vendors')->insertOrIgnore(['code' => $code, 'name' => $name, 'website' => $website, 'is_active' => true] + $ts);
            }
            $branches = DB::table('branches')->pluck('id', 'code');
            $vendors = DB::table('vendors')->pluck('id', 'code');

            foreach (self::MODELS as [$vendor, $name, $category, $desc]) {
                DB::table('device_models')->insertOrIgnore([
                    'vendor_id' => $vendors[$vendor], 'name' => $name, 'category' => $category, 'description' => $desc, 'is_active' => true,
                ] + $ts);
            }
            foreach (self::SOFTWARE as [$vendor, $name, $edition, $category, $desc]) {
                DB::table('software_products')->insertOrIgnore([
                    'vendor_id' => $vendors[$vendor], 'name' => $name, 'edition' => $edition, 'category' => $category,
                    'description' => $desc, 'is_active' => true,
                ] + $ts);
            }
            $models = DB::table('device_models')->get(['id', 'vendor_id', 'name'])->keyBy('name');

            // Perangkat + lisensi
            $year = $now->format('Y');
            $licSeq = $this->lastSequence('licenses', 'code', "LIC-{$year}-");
            $rnwSeq = $this->lastSequence('license_renewals', 'request_number', "RNW-{$year}-");

            $groups = [
                [self::PALO_ALTO, 'subscription', fn ($m) => "Palo Alto {$m} Subscription"],
                [self::SANGFOR, 'subscription', fn ($m) => "Sangfor {$m} License"],
                [self::ARUBA, 'warranty', fn ($m) => 'HPE Foundation Care NBD Exchange SVC'],
            ];

            foreach ($groups as [$rows, $licenseType, $nameFn]) {
                foreach ($rows as [$branch, $modelName, $sn, $start, $end]) {
                    if (DB::table('devices')->where('serial_number', $sn)->exists()) {
                        continue; // sudah pernah diimpor / diinput manual
                    }
                    $model = $models[$modelName];

                    $deviceId = DB::table('devices')->insertGetId([
                        'branch_id' => $branches[$branch], 'device_model_id' => $model->id,
                        'serial_number' => $sn, 'status' => 'active',
                    ] + $ts);

                    $licenseId = DB::table('licenses')->insertGetId([
                        'code' => sprintf('LIC-%s-%05d', $year, ++$licSeq),
                        'asset_type' => 'hardware', 'license_type' => $licenseType,
                        'vendor_id' => $model->vendor_id, 'branch_id' => $branches[$branch], 'device_id' => $deviceId,
                        'name' => $nameFn($modelName), 'key_format' => 'none',
                        'start_date' => $start, 'end_date' => $end, 'currency' => 'IDR',
                    ] + $ts);

                    if (in_array($sn, self::ARUBA_HISTORY_SN, true)) {
                        DB::table('license_renewals')->insert([
                            'request_number' => sprintf('RNW-%s-%05d', $year, ++$rnwSeq),
                            'license_id' => $licenseId, 'status' => 'completed',
                            'old_name' => 'HPE Foundation Care 24x7 SVC', 'old_start_date' => '2025-12-15', 'old_end_date' => '2026-10-31',
                            'new_name' => 'HPE Foundation Care NBD Exchange SVC', 'new_start_date' => $start, 'new_end_date' => $end,
                            'reason' => 'Data historis dari Excel (tabel atas sheet ARUBA).',
                            'completed_at' => $start.' 00:00:00',
                        ] + $ts);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        $serials = collect([self::PALO_ALTO, self::SANGFOR, self::ARUBA])->flatten(1)->pluck(2);

        DB::transaction(function () use ($serials) {
            $deviceIds = DB::table('devices')->whereIn('serial_number', $serials)->pluck('id');
            $licenseIds = DB::table('licenses')->whereIn('device_id', $deviceIds)->pluck('id');

            DB::table('license_renewals')->whereIn('license_id', $licenseIds)->delete();
            DB::table('licenses')->whereIn('id', $licenseIds)->delete();
            DB::table('devices')->whereIn('id', $deviceIds)->delete();
            // Master & settings sengaja tidak dihapus: bisa sudah dipakai data lain (user, lisensi baru).
        });
    }

    private function lastSequence(string $table, string $column, string $prefix): int
    {
        $last = DB::table($table)->where($column, 'like', $prefix.'%')->max($column);

        return $last ? (int) substr($last, strlen($prefix)) : 0;
    }
};
