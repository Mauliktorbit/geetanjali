<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Courier extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'code',
  2 => 'provider',
  3 => 'credentials',
  4 => 'is_active',
  5 => 'settings',
);

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
