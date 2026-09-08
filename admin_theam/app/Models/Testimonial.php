<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Testimonial extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'designation',
  2 => 'avatar',
  3 => 'content',
  4 => 'rating',
  5 => 'is_active',
  6 => 'sort_order',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
