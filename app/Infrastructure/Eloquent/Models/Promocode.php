<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promocode extends Model
{
    protected $fillable = ['code', 'discount_id'];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
