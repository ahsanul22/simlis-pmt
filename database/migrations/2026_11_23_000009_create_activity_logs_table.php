<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Audit trail: siapa melakukan apa, kapan, dan perubahan datanya (before/after). */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 50)->index(); // created, updated, deleted, login, renewal.approved, ...
            $table->nullableMorphs('subject');
            $table->string('description')->nullable();
            $table->json('properties')->nullable(); // {"old": {...}, "new": {...}}
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
