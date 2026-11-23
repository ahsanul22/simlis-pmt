<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan reminder yang sudah terkirim, agar scheduler harian tidak mengirim ulang
     * untuk ambang yang sama (mis. H-90, H-60, H-30, H-14, H-7, H-1).
     * end_date ikut dicatat: setelah diperpanjang, reminder untuk periode baru bisa terkirim lagi.
     */
    public function up(): void
    {
        Schema::create('license_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->date('end_date');
            $table->unsignedSmallInteger('threshold_days');
            $table->string('channel', 20)->default('mail'); // mail | database
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamp('sent_at');

            $table->unique(['license_id', 'end_date', 'threshold_days', 'channel'], 'reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_reminder_logs');
    }
};
