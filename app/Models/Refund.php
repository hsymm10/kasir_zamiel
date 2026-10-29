<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id', 'transaction_item_id', 'user_id', 
    'product_id', 'product_name', 'qty', 'refund_amount', 
    'reason', 'is_restocked', 'notes'
])]
class Refund extends Model
{
    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'is_restocked' => 'boolean',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
