<?php

namespace Database\Factories;

use App\Enums\ScanResult;
use App\Models\BarcodeGeneration;
use App\Models\ScanLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScanLog>
 */
class ScanLogFactory extends Factory
{
    protected $model = ScanLog::class;

    public function definition(): array
    {
        return [
            'scanned_by' => User::factory(),
            'barcode_generation_id' => BarcodeGeneration::factory(),
            'unique_code' => fake()->unique()->bothify('BC-########'),
            'raw_scan_data' => fake()->sentence(),
            'scan_result' => ScanResult::Success,
            'product_data_snapshot' => [
                'name' => fake()->words(2, true),
                'sku' => fake()->bothify('SKU-####'),
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
