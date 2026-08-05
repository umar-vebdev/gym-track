<?php

namespace App\Modules\Clients\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс клиента для API-ответов.
 *
 * @mixin \App\Modules\Clients\Models\Client
 *
 * @property int    $id
 * @property string $client_code
 * @property string $full_name
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 */
class ClientResource extends JsonResource
{
    /**
     * Преобразование клиента в JSON-формат.
     *
     * @return array{
     *   id: int,
     *   client_code: string,
     *   full_name: string,
     *   phone: string,
     *   created_at: string,
     *   updated_at: string,
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'client_code' => $this->client_code,
            'full_name'   => $this->full_name,
            'phone'       => $this->phone,
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),
            'membership_purchases' => $this->whenLoaded('membershipPurchases'),
            'visits'      => $this->whenLoaded('visits'),
            'product_sales' => $this->whenLoaded('productSales'),
        ];
    }
}
