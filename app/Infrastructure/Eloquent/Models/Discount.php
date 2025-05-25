<?php
declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    public function promocodes(): hasMany
    {
        return $this->hasMany(Promocode::class);
    }
}
