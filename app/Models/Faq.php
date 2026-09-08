<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'question',
  1 => 'answer',
  2 => 'category',
  3 => 'sort_order',
  4 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
