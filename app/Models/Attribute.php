<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'slug',
  2 => 'type',
  3 => 'is_active',
  4 => 'sort_order',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }
}
