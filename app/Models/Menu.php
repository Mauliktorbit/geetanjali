<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'location',
  2 => 'is_active',
);

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
