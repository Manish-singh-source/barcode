<?php

namespace App\Models;

use App\Enums\BarcodeFormat;
use Database\Factories\BarcodeGenerationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int
 * @property int
 * @property string
 * @property BarcodeFormat
 * @property string
 * @property string|null
 * @property string|null
 * @property bool
 */
class BarcodeGeneration extends Model
{
    /** @use HasFactory<BarcodeGenerationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'unique_code',
        'barcode_format',
        'barcode_data',
        'barcode_image_path',
        'custom_label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'barcode_format' => BarcodeFormat::class,
            'is_active' => 'boolean',
            'user_id' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    /**
     * Build a product-like snapshot from barcode_data so the app can work
     * without any linked product table.
     *
     * @return array<string, mixed>
     */
    public function resolvedProductSnapshot(): array
    {
        $decoded = json_decode((string) $this->barcode_data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $name = $decoded['name'] ?? $decoded['product_name'] ?? $decoded['title'] ?? $decoded['sku'] ?? $this->barcode_data;

            return [
                'id' => null,
                'name' => $name,
                'sku' => $decoded['sku'] ?? null,
                'description' => $decoded['description'] ?? null,
                'price' => $decoded['price'] ?? null,
                'brand' => $decoded['brand'] ?? null,
                'category' => $decoded['category'] ?? null,
                'unit' => $decoded['unit'] ?? null,
                'stock_quantity' => $decoded['stock_quantity'] ?? null,
                'raw' => $this->barcode_data,
            ];
        }

        $text = trim((string) $this->barcode_data);

        return [
            'id' => null,
            'name' => $text !== '' ? $text : $this->unique_code,
            'sku' => null,
            'description' => null,
            'price' => null,
            'brand' => null,
            'category' => null,
            'unit' => null,
            'stock_quantity' => null,
            'raw' => $this->barcode_data,
        ];
    }
}
