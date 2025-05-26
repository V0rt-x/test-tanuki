<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Cart\Models\CartProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getId(),
            'user_id' => $this->resource->getUserId(),
            'cart_products' => CartProductResource::collection($this->resource->getCartProducts()),
            'promocode' => $this->resource->getPromocode() ? PromocodeResource::make($this->resource->getPromocode()) : null,
            'discount' => [], // TODO
            'total_base_sum' => $this->resource->totalBaseSum(),
            'total_final_sum' => $this->resource->totalFinalSum(),
        ];
    }
}
