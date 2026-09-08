<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRate extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'hsn_sac',
  2 => 'cgst',
  3 => 'sgst',
  4 => 'igst',
  5 => 'is_inclusive',
  6 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'cgst' => 'decimal:2',
            'sgst' => 'decimal:2',
            'igst' => 'decimal:2',
        ];
    }
}
