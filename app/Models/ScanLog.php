<?php

namespace App\Models;

use App\Enums\ScanResult;
use Database\Factories\ScanLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int 
 * @property int|null 
 * @property int|null 
 * @property string 
 * @property string 
 * @property ScanResult 
 * @property array<string, mixed>|null 
 * @property string|null 
 * @property string|null 
 */
class ScanLog extends Model
{
    /** @use HasFactory<ScanLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'scanned_by',
        'barcode_generation_id',
        'unique_code',
        'raw_scan_data',
        'scan_result',
        'product_data_snapshot',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scan_result' => ScanResult::class,
            'product_data_snapshot' => 'array',
            'scanned_by' => 'integer',
            'barcode_generation_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    /**
     * @return BelongsTo<BarcodeGeneration, $this>
     */
    public function barcodeGeneration(): BelongsTo
    {
        return $this->belongsTo(BarcodeGeneration::class);
    }
}
