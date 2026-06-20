<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barcode_generations', function (Blueprint $table): void {
            if (Schema::hasColumn('barcode_generations', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });

        Schema::dropIfExists('products');
    }

    public function down(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('barcode_generations', function (Blueprint $table): void {
            if (! Schema::hasColumn('barcode_generations', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
            }
        });
    }
};