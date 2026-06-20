<?php

namespace App\Models;

use App\Enums\BarcodeFormat;
use Database\Factories\BarcodeGenerationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int 
 * @property int 
 * @property int|null 
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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'unique_code',
        'barcode_format',
        'barcode_data',
        'barcode_image_path',
        'custom_label',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'barcode_format' => BarcodeFormat::class,
            'is_active' => 'boolean',
            'user_id' => 'integer',
            'product_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<ScanLog, $this>
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}
