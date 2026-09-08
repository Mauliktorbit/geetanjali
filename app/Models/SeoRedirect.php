<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoRedirect extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'from_path',
  1 => 'to_path',
  2 => 'status_code',
  3 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
