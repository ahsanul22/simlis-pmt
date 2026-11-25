<?php

namespace App\Services;

use App\Enums\AssetType;
use App\Models\Branch;
use App\Models\License;
use App\Models\User;
use App\Models\Vendor;
use App\Support\ExpiryPeriod;
use Illuminate\Support\Collection;

/**
 * Laporan lisensi untuk perencanaan anggaran (RAB) perpanjangan.
 *
 * Isi: lisensi (belum dihentikan, punya end date) yang berakhir dalam periode filter,
 * mengikuti scope user (PIC Branch hanya branch-nya).
 *
 * - details()  : satu baris per lisensi
 * - summary()  : rekap per item (vendor + perangkat/produk + jenis + paket) → qty, lokasi, harga
 * - byMonth()  : rekap per bulan berakhir per item → kapan anggaran dibutuhkan
 *
 * Qty: lisensi hardware = 1 unit; software dengan jumlah seat = seat-nya.
 * Harga satuan = total nilai lisensi yang sudah diisi ÷ qty-nya (null bila belum ada nilai sama sekali).
 */
class LicenseReport
{
    public const FILTER_KEYS = ['vendor', 'branch', 'asset_type', ...ExpiryPeriod::KEYS];

    public readonly ExpiryPeriod $period;

    private ?Collection $licenses = null;

    public function __construct(private readonly User $user, public readonly array $filters)
    {
        $this->period = ExpiryPeriod::fromArray($filters);
    }

    public static function for(User $user, array $input): self
    {
        $filters = collect($input)->only(self::FILTER_KEYS)->filter(fn ($v) => $v !== null && $v !== '')->all();

        if ($user->isBranchScoped()) {
            unset($filters['branch']);
        }

        return new self($user, $filters);
    }

    /** @return Collection<int, License> */
    public function licenses(): Collection
    {
        return $this->licenses ??= License::visibleTo($this->user)->active()
            ->whereNotNull('end_date')
            ->filter($this->filters)
            ->with(['vendor:id,name', 'branch:id,name', 'device:id,device_model_id,serial_number,hostname', 'device.model:id,name', 'softwareProduct:id,name,edition'])
            ->orderBy('end_date')
            ->get();
    }

    /** Satu baris per lisensi. */
    public function details(): Collection
    {
        return $this->licenses()->map(fn (License $l) => [
            'code' => $l->code,
            'vendor' => $l->vendor?->name,
            'item' => $this->itemName($l),
            'serial_number' => $l->device?->serial_number,
            'branch' => $l->branch?->name,
            'license_type' => $l->license_type?->label(),
            'package' => $l->name,
            'qty' => $this->qty($l),
            'unit' => $this->unit($l),
            'start_date' => $l->start_date,
            'end_date' => $l->end_date,
            'days_remaining' => $l->days_remaining,
            'status' => $l->expiry_status->label(),
            'cost' => $l->cost !== null ? (float) $l->cost : null,
        ]);
    }

    /** Rekap per item untuk RAB. */
    public function summary(): Collection
    {
        return $this->licenses()
            ->groupBy(fn (License $l) => $this->groupKey($l))
            ->map(fn (Collection $rows) => $this->summarize($rows))
            ->sortBy([['first_end', 'asc'], ['vendor', 'asc'], ['item', 'asc']])
            ->values();
    }

    /** Rekap per bulan berakhir × item. */
    public function byMonth(): Collection
    {
        return $this->licenses()
            ->groupBy(fn (License $l) => $l->end_date->format('Y-m').'|'.$this->groupKey($l))
            ->map(fn (Collection $rows) => $this->summarize($rows) + [
                'month' => $rows->first()->end_date->copy()->startOfMonth(),
            ])
            ->sortBy([['month', 'asc'], ['vendor', 'asc'], ['item', 'asc']])
            ->values();
    }

    public function totalQty(): int
    {
        return (int) $this->licenses()->sum(fn (License $l) => $this->qty($l));
    }

    /** Total estimasi dari item yang punya harga; null bila belum ada harga sama sekali. */
    public function totalCost(): ?float
    {
        $priced = $this->summary()->whereNotNull('total');

        return $priced->isEmpty() ? null : (float) $priced->sum('total');
    }

    /** Keterangan filter untuk header Excel/PDF. */
    public function filterLabels(): array
    {
        $labels = ['Periode berakhir' => $this->period->label()];

        if ($id = $this->filters['vendor'] ?? null) {
            $labels['Vendor'] = Vendor::whereKey($id)->value('name') ?? '-';
        }
        if ($this->user->isBranchScoped()) {
            $labels['Lokasi'] = $this->user->branch?->name ?? '-';
        } elseif ($id = $this->filters['branch'] ?? null) {
            $labels['Lokasi'] = Branch::whereKey($id)->value('name') ?? '-';
        }
        if ($type = AssetType::tryFrom($this->filters['asset_type'] ?? '')) {
            $labels['Jenis aset'] = $type->label();
        }

        return $labels;
    }

    /** Nama file tanpa ekstensi, mis. laporan-lisensi_2027-01_sd_2027-12. */
    public function fileName(): string
    {
        $p = $this->period;
        $range = match (true) {
            $p->start && $p->end => $p->start->format('Y-m').'_sd_'.$p->end->format('Y-m'),
            $p->start !== null => 'mulai_'.$p->start->format('Y-m'),
            $p->end !== null => 'sampai_'.$p->end->format('Y-m'),
            default => 'semua-periode',
        };

        return 'laporan-lisensi_'.$range.'_'.now()->format('Ymd');
    }

    private function summarize(Collection $rows): array
    {
        /** @var License $first */
        $first = $rows->first();
        $qty = (int) $rows->sum(fn (License $l) => $this->qty($l));

        $priced = $rows->filter(fn (License $l) => $l->cost !== null && (float) $l->cost > 0);
        $pricedQty = $priced->sum(fn (License $l) => $this->qty($l));
        $unitPrice = $pricedQty > 0 ? round($priced->sum(fn (License $l) => (float) $l->cost) / $pricedQty) : null;

        return [
            'vendor' => $first->vendor?->name,
            'item' => $this->itemName($first),
            'license_type' => $first->license_type?->label(),
            'package' => $first->name,
            'qty' => $qty,
            'unit' => $this->unit($first),
            'first_end' => $rows->min('end_date'),
            'last_end' => $rows->max('end_date'),
            'branches' => $rows->map(fn (License $l) => $l->branch?->name)->filter()->unique()->sort()->values()->all(),
            'unit_price' => $unitPrice,
            'total' => $unitPrice !== null ? $unitPrice * $qty : null,
        ];
    }

    private function groupKey(License $l): string
    {
        return implode('|', [$l->vendor_id, $l->device?->device_model_id ?? 'sw'.$l->software_product_id, $l->license_type?->value, $l->name, $this->unit($l)]);
    }

    private function itemName(License $l): string
    {
        if ($l->device) {
            return $l->device->model?->name ?? '-';
        }

        return trim(($l->softwareProduct?->name ?? '-').' '.($l->softwareProduct?->edition ?? ''));
    }

    private function qty(License $l): int
    {
        return $l->asset_type === AssetType::SOFTWARE && $l->seats > 0 ? (int) $l->seats : 1;
    }

    private function unit(License $l): string
    {
        if ($l->asset_type === AssetType::HARDWARE) {
            return 'unit';
        }

        return $l->seats > 0 ? 'user' : 'lisensi';
    }
}
