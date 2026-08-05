<?php

namespace App\Modules\Products\Models;

use App\Modules\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'quantity',
        'total_price',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_price' => 'float',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
