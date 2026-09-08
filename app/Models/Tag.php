<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'slug',
);

    protected function casts(): array
    {
        return [
        ];
    }
}
