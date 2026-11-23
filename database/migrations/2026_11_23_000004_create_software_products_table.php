<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Produk software: Microsoft 365, Windows Server, Antivirus, AutoCAD, dst. */
    public function up(): void
    {
        Schema::create('software_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('edition', 100)->nullable();
            $table->string('category', 50)->nullable(); // OS, Office, Antivirus, Database, Desain, ...
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vendor_id', 'name', 'edition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_products');
    }
};
