<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('barcode_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unique_code');
            $table->text('raw_scan_data');
            $table->enum('scan_result', ['success', 'invalid']);
            $table->json('product_data_snapshot')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
