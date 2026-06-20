<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int 
 * @property string 
 * @property string|null 
 * @property string|null 
 * @property string|null 
 * @property string|null 
 * @property string|null 
 * @property string|null 
 * @property int 
 * @property array<string, mixed>|null 
 * @property bool 
 * @property int|null 
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'category',
        'brand',
        'unit',
        'stock_quantity',
        'meta',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'meta' => 'array',
            'is_active' => 'boolean',
            'created_by' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<BarcodeGeneration, $this>
     */
    public function barcodeGenerations(): HasMany
    {
        return $this->hasMany(BarcodeGeneration::class);
    }
}
