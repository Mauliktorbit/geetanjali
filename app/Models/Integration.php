<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Integration extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'provider',
  2 => 'category',
  3 => 'credentials',
  4 => 'settings',
  5 => 'is_active',
  6 => 'is_sandbox',
);

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_sandbox' => 'boolean',
        ];
    }
}
