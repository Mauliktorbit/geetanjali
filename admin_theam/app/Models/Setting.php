<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean', 'bool' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $this->value,
            'float', 'decimal' => (float) $this->value,
            'json', 'array' => json_decode($this->value ?? '[]', true),
            default => $this->value,
        };
    }
}
