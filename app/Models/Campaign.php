<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'name',
  1 => 'channel',
  2 => 'type',
  3 => 'subject',
  4 => 'content',
  5 => 'segment',
  6 => 'status',
  7 => 'scheduled_at',
  8 => 'sent_at',
  9 => 'sent_count',
  10 => 'created_by',
);

    protected function casts(): array
    {
        return [
            'segment' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
