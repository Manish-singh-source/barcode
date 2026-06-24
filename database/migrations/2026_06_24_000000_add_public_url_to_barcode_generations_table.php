<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barcode_generations', function (Blueprint $table) {
            $table->string('public_url')->nullable()->after('barcode_image_path');
        });

        DB::table('barcode_generations')
            ->select('id', 'unique_code')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('barcode_generations')
                        ->where('id', $row->id)
                        ->update([
                            'public_url' => url('/b/' . $row->unique_code),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('barcode_generations', function (Blueprint $table) {
            $table->dropColumn('public_url');
        });
    }
};
