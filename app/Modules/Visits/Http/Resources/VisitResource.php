<?php

namespace App\Modules\Visits\Http\Resources;

use App\Modules\Clients\Http\Resources\ClientResource;
use App\Modules\Memberships\Http\Resources\MembershipPurchaseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс визита для API-ответов.
 *
 * @mixin \App\Modules\Visits\Models\Visit
 *
 * @property int    $id
 * @property int    $client_id
 * @property int    $membership_purchase_id
 * @property string $visited_at
 * @property string $created_at
 * @property string $updated_at
 */
class VisitResource extends JsonResource
{
    /**
     * @return array{
     *   id: int,
     *   client_id: int,
     *   membership_purchase_id: int,
     *   visited_at: string,
     *   client: ClientResource|\Illuminate\Http\Resources\MissingValue,
     *   membership_purchase: MembershipPurchaseResource|\Illuminate\Http\Resources\MissingValue,
     *   created_at: string,
     *   updated_at: string,
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'client_id'              => $this->client_id,
            'membership_purchase_id' => $this->membership_purchase_id,
            'visited_at'             => $this->visited_at->toISOString(),
            'client'                 => new ClientResource($this->whenLoaded('client')),
            'membership_purchase'    => new MembershipPurchaseResource($this->whenLoaded('membershipPurchase')),
            'created_at'             => $this->created_at->toISOString(),
            'updated_at'             => $this->updated_at->toISOString(),
        ];
    }
}
