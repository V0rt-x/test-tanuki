<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    public function cartProducts(): HasMany
    {
        return $this->hasMany(CartProduct::class);
    }
}
