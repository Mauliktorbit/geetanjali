<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnReason extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'type',
  2 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
