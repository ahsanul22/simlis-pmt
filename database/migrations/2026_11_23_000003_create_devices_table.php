<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unit perangkat fisik (aset). Dipisah dari lisensi karena satu perangkat bisa punya
     * beberapa lisensi sekaligus (subscription + support/warranty) dan riwayatnya.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('device_model_id')->constrained('device_models')->restrictOnDelete();
            $table->string('serial_number', 100)->unique();
            $table->string('hostname', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('active')->index(); // App\Enums\DeviceStatus
            $table->date('installed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
