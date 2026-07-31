<?php

namespace App\Modules\Memberships\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс типа абонемента.
 *
 * @mixin \App\Modules\Memberships\Models\MembershipType
 *
 * @property int    $id
 * @property string $name
 * @property string $duration_type
 * @property int    $duration_value
 * @property float  $price
 * @property bool   $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class MembershipTypeResource extends JsonResource
{
    /**
     * @return array{
     *   id: int,
     *   name: string,
     *   duration_type: string,
     *   duration_value: int,
     *   price: float,
     *   is_active: bool,
     *   created_at: string,
     *   updated_at: string,
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'duration_type'  => $this->duration_type,
            'duration_value' => $this->duration_value,
            'price'          => $this->price,
            'is_active'      => $this->is_active,
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}
