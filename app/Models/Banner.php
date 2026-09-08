<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'title',
  1 => 'type',
  2 => 'image',
  3 => 'mobile_image',
  4 => 'link',
  5 => 'content',
  6 => 'starts_at',
  7 => 'ends_at',
  8 => 'sort_order',
  9 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
