<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengajuan + riwayat perpanjangan lisensi (menggantikan "Log Riwayat" di referensi).
     * Baris berstatus completed = arsip periode lama (old_*) -> periode baru (new_*).
     */
    public function up(): void
    {
        Schema::create('license_renewals', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 30)->unique(); // RNW-2026-00001
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('status', 20)->default('draft')->index(); // App\Enums\RenewalStatus

            // Snapshot periode lama (diambil saat pengajuan dibuat)
            $table->string('old_name', 150)->nullable();
            $table->text('old_license_key')->nullable(); // encrypted
            $table->date('old_start_date')->nullable();
            $table->date('old_end_date')->nullable();

            // Periode baru (usulan saat pengajuan, final saat completed)
            $table->string('new_name', 150)->nullable();
            $table->text('new_license_key')->nullable(); // encrypted
            $table->date('new_start_date')->nullable();
            $table->date('new_end_date')->nullable();

            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->text('reason')->nullable();

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['license_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_renewals');
    }
};
