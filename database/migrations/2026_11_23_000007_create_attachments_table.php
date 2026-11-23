<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lampiran polimorfik (license / license_renewal / device):
     * foto bukti lisensi, foto stiker SN, PO, invoice, kontrak, BAST.
     * File disimpan di storage, DB hanya simpan path (bukan base64 seperti di referensi).
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('category', 30)->index(); // App\Enums\AttachmentCategory
            $table->string('disk', 20)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->string('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
