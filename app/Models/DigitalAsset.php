<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_path',
        'file_name',
        'download_limit',
        'expiry_days',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
