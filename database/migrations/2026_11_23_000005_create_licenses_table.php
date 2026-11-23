<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lisensi AKTIF saat ini (satu baris = satu lisensi berjalan).
     * Periode lama tidak ditimpa begitu saja: setiap perpanjangan tercatat di license_renewals.
     *
     * asset_type = hardware -> device_id wajib
     * asset_type = software -> software_product_id wajib
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // LIC-2026-00001
            $table->string('asset_type', 20)->index(); // App\Enums\AssetType
            $table->string('license_type', 20)->index(); // App\Enums\LicenseType
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->restrictOnDelete();
            $table->foreignId('software_product_id')->nullable()->constrained('software_products')->restrictOnDelete();

            $table->string('name', 150); // nama paket, mis. "HPE Foundation Care NBD Exchange SVC"
            // Tidak semua lisensi punya key (App\Enums\LicenseKeyFormat):
            // none = terikat SN/portal vendor, text = license_key terenkripsi, image = lampiran kategori license_key
            $table->string('key_format', 10)->default('none');
            $table->text('license_key')->nullable(); // dienkripsi (cast encrypted), hanya untuk key_format = text
            $table->unsignedInteger('seats')->nullable(); // jumlah user/seat (software)
            $table->unsignedInteger('seats_used')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable()->index(); // null = perpetual

            $table->decimal('cost', 15, 2)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->string('contract_number', 100)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->string('supplier', 150)->nullable(); // distributor/reseller pengadaan

            $table->boolean('is_terminated')->default(false); // tidak diperpanjang lagi (sengaja dihentikan)
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
