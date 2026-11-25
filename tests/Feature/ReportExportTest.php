<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LicenseReport;
use App\Support\ExpiryPeriod;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Filter masa berakhir (bulan/tahun) + export laporan RAB Excel/PDF.
 * Data = import Excel di migration: 25 Palo Alto, 21 Sangfor, 9 Aruba.
 * - Okt 2027 : 23 PA-440 + 1 PA-3410 + 20 Sangfor + 9 Aruba = 53
 * - Feb 2027 : 1 PA-440 (Bima)
 * - Okt 2026 : 1 Sangfor IAG 51 (Bima)
 */
class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-25 09:00:00');
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    public function test_expiry_period_parsing(): void
    {
        $p = ExpiryPeriod::fromArray(['from_year' => 2027, 'to_year' => 2027]);
        $this->assertSame('2027-01-01', $p->start->toDateString());
        $this->assertSame('2027-12-31', $p->end->toDateString());

        $swapped = ExpiryPeriod::fromArray(['from_month' => 6, 'from_year' => 2028, 'to_month' => 2, 'to_year' => 2027]);
        $this->assertSame('2027-02-01', $swapped->start->toDateString());
        $this->assertSame('2028-06-30', $swapped->end->toDateString());

        $this->assertFalse(ExpiryPeriod::fromArray(['from_month' => 13, 'from_year' => 'abc'])->isSet());
        $this->assertSame('Semua periode', ExpiryPeriod::fromArray([])->label());
    }

    public function test_license_list_can_be_filtered_by_expiry_month_and_year(): void
    {
        $this->actingAs($this->user('admin.ti'));

        $this->get(route('admin-ti.licenses.index', ['from_month' => 2, 'from_year' => 2027, 'to_month' => 2, 'to_year' => 2027]))
            ->assertOk()
            ->assertViewHas('licenses', fn ($p) => $p->total() === 1)
            ->assertSee('Februari 2027');

        $this->get(route('admin-ti.licenses.index', ['from_year' => 2027, 'to_year' => 2027]))
            ->assertViewHas('licenses', fn ($p) => $p->total() === 54);

        $this->actingAs($this->user('pic.bima'));
        $this->get(route('pic-branch.licenses.index', ['from_year' => 2026, 'to_year' => 2026]))
            ->assertOk()
            ->assertViewHas('licenses', fn ($p) => $p->total() === 1);
    }

    public function test_report_groups_quantity_per_item(): void
    {
        $report = LicenseReport::for($this->user('admin.ti'), ['from_month' => 10, 'from_year' => 2027, 'to_month' => 10, 'to_year' => 2027]);
        $qty = $report->summary()->mapWithKeys(fn ($s) => [$s['item'] => $s['qty']]);

        $this->assertSame(53, $report->totalQty());
        $this->assertSame(23, $qty['PA-440']);
        $this->assertSame(1, $qty['PA-3410']);
        $this->assertSame(16, $qty['IAG 51']);
        $this->assertSame(6, $qty['HPE 5520']);
        $this->assertNull($report->totalCost()); // data Excel belum punya harga
    }

    public function test_report_page_shows_rab_section_for_period(): void
    {
        $this->actingAs($this->user('manajemen'));

        $this->get(route('manajemen.reports.index', ['from_year' => 2027, 'to_year' => 2027]))
            ->assertOk()
            ->assertSee('Rencana Perpanjangan · Januari 2027 – Desember 2027')
            ->assertSee('from_year=2027', false); // link unduh membawa filter
    }

    public function test_excel_export_contains_rab_sheets_with_quantity(): void
    {
        $this->actingAs($this->user('admin.ti'));

        $response = $this->get(route('admin-ti.reports.export', ['format' => 'xlsx', 'from_year' => 2027, 'to_year' => 2027]));
        $response->assertOk();
        $this->assertStringContainsString('laporan-lisensi_2027-01_sd_2027-12', $response->headers->get('content-disposition'));

        $book = IOFactory::load($response->getFile()->getPathname());
        $this->assertSame(['Rekap RAB', 'Per Bulan', 'Rincian'], $book->getSheetNames());

        $rekap = $book->getSheetByName('Rekap RAB');
        $rows = collect($rekap->toArray(null, true, false, false));
        $pa440 = $rows->first(fn ($r) => ($r[2] ?? null) === 'PA-440');
        $this->assertSame(24, (int) $pa440[5]);
        $this->assertNotNull($rows->first(fn ($r) => ($r[0] ?? null) === 'TOTAL'));

        $rincian = collect($book->getSheetByName('Rincian')->toArray(null, true, false, false));
        $this->assertNotNull($rincian->first(fn ($r) => ($r[4] ?? null) === '021209007575'), 'SN dengan nol di depan harus utuh');

        @unlink($response->getFile()->getPathname());
    }

    public function test_pdf_export_and_access_rules(): void
    {
        $this->actingAs($this->user('manajemen'));
        $pdf = $this->get(route('manajemen.reports.export', ['format' => 'pdf', 'from_year' => 2027, 'to_year' => 2027]));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->get(route('manajemen.reports.export', ['format' => 'docx']))->assertNotFound();

        $this->actingAs($this->user('pic.bima'));
        $this->get(route('admin-ti.reports.export', ['format' => 'xlsx']))->assertForbidden();
    }
}
