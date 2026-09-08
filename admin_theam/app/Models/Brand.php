<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = array (
  0 => 'name',
  1 => 'slug',
  2 => 'logo',
  3 => 'description',
  4 => 'seo_title',
  5 => 'seo_description',
  6 => 'seo_keywords',
  7 => 'is_active',
  8 => 'sort_order',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
