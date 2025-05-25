<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class CartProduct extends Model
{
    protected $fillable = [
        'product_id', 'quantity', 'base_price', 'final_price'
    ];
}
