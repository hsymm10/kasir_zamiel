<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'start_time', 'end_time', 'starting_cash', 
    'expected_ending_cash', 'actual_ending_cash', 'difference', 
    'status', 'notes'
])]
class CashierShift extends Model
{
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'starting_cash' => 'decimal:2',
            'expected_ending_cash' => 'decimal:2',
            'actual_ending_cash' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
