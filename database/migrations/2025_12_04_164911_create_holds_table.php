<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('slots')->cascadeOnDelete();
            $table->string('idempotency_key')->nullable()->unique();
            $table->enum('status', ['held','confirmed','cancelled'])->default('held');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
          //  $table->index(['status', 'created_at']);
            $table->index(['slot_id', 'status'], 'idx_holds_slot_status');
            //$table->index(['status', 'expires_at'], 'idx_holds_cleanup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
