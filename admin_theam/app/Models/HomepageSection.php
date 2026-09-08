<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomepageSection extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'title',
  1 => 'type',
  2 => 'settings',
  3 => 'sort_order',
  4 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
