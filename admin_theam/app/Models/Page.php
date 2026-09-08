<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'title',
  1 => 'slug',
  2 => 'type',
  3 => 'content',
  4 => 'seo_title',
  5 => 'seo_description',
  6 => 'canonical',
  7 => 'og_image',
  8 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
