<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'name', 'barcode', 'image', 'price_buy', 'price_sell', 'discount_persen', 'stock', 'expiration_date'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'expiration_date'  => 'date',
            'price_buy'        => 'decimal:2',
            'price_sell'       => 'decimal:2',
            'discount_persen'  => 'decimal:2',
            'stock'            => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
