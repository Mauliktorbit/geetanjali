<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = array (
  0 => 'category',
  1 => 'title',
  2 => 'amount',
  3 => 'expense_date',
  4 => 'payment_method',
  5 => 'reference',
  6 => 'notes',
  7 => 'attachment',
  8 => 'created_by',
);

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }
}
