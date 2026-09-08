<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Blog extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'title',
  1 => 'slug',
  2 => 'image',
  3 => 'excerpt',
  4 => 'content',
  5 => 'seo_title',
  6 => 'seo_description',
  7 => 'is_published',
  8 => 'published_at',
  9 => 'author_id',
);

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
