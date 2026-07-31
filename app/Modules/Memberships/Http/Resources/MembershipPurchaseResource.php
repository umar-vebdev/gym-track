<?php

namespace App\Modules\Memberships\Http\Resources;

use App\Modules\Clients\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс проданного абонемента.
 *
 * @mixin \App\Modules\Memberships\Models\MembershipPurchase
 *
 * @property int         $id
 * @property int         $client_id
 * @property int         $membership_type_id
 * @property float       $amount_paid
 * @property string      $starts_at
 * @property string|null $expires_at
 * @property int|null    $visits_left
 * @property string|null $payment_method
 * @property bool        $is_active
 * @property string      $created_at
 * @property string      $updated_at
 */
class MembershipPurchaseResource extends JsonResource
{
    /**
     * @return array{
     *   id: int,
     *   client_id: int,
     *   membership_type_id: int,
     *   amount_paid: float,
     *   starts_at: string,
     *   expires_at: string|null,
     *   visits_left: int|null,
     *   payment_method: string|null,
     *   is_active: bool,
     *   client: ClientResource|\Illuminate\Http\Resources\MissingValue,
     *   membership_type: MembershipTypeResource|\Illuminate\Http\Resources\MissingValue,
     *   created_at: string,
     *   updated_at: string,
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'client_id'          => $this->client_id,
            'membership_type_id' => $this->membership_type_id,
            'amount_paid'        => $this->amount_paid,
            'starts_at'          => $this->starts_at->format('Y-m-d'),
            'expires_at'         => $this->expires_at?->format('Y-m-d'),
            'visits_left'        => $this->visits_left,
            'payment_method'     => $this->payment_method,
            'is_active'          => $this->isActive(),
            'client'             => new ClientResource($this->whenLoaded('client')),
            'membership_type'    => new MembershipTypeResource($this->whenLoaded('membershipType')),
            'created_at'         => $this->created_at->toISOString(),
            'updated_at'         => $this->updated_at->toISOString(),
        ];
    }
}
