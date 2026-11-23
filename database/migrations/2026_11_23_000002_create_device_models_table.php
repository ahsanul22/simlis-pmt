<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tipe/model perangkat: PA-440, PA-3410, IAG 51, HPE 5520, ... */
    public function up(): void
    {
        Schema::create('device_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('category', 30)->index(); // App\Enums\DeviceCategory
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vendor_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_models');
    }
};
