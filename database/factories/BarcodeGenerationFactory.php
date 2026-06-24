<?php

namespace Database\Factories;

use App\Enums\BarcodeFormat;
use App\Models\BarcodeGeneration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BarcodeGeneration>
 */
class BarcodeGenerationFactory extends Factory
{
    protected $model = BarcodeGeneration::class;

    public function definition(): array
    {
        $uniqueCode = fake()->unique()->bothify('BC-########');

        return [
            'user_id' => User::factory(),
            'unique_code' => $uniqueCode,
            'barcode_format' => BarcodeFormat::Code128,
            'barcode_data' => fake()->bothify('DATA-########'),
            'barcode_image_path' => null,
            'public_url' => url('/b/' . $uniqueCode),
            'custom_label' => fake()->optional()->words(2, true),
            'is_active' => true,
        ];
    }
}
