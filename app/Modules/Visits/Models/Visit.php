<?php

namespace App\Modules\Visits\Models;

use App\Modules\Clients\Models\Client;
use App\Modules\Memberships\Models\MembershipPurchase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель визита (посещения зала).
 *
 * @property int            $id
 * @property int            $client_id              ID клиента
 * @property int            $membership_purchase_id ID абонемента, по которому сделан визит
 * @property \Carbon\Carbon $visited_at             Дата и время визита
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Client $client
 * @property-read MembershipPurchase $membershipPurchase
 */
class Visit extends Model
{
    use HasFactory;

    /**
     * Создать новый экземпляр фабрики для модели.
     */
    protected static function newFactory(): \Database\Factories\VisitFactory
    {
        return \Database\Factories\VisitFactory::new();
    }

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'membership_purchase_id',
        'visited_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id'              => 'integer',
            'membership_purchase_id' => 'integer',
            'visited_at'             => 'datetime',
        ];
    }

    /**
     * Посетитель (клиент).
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Абонемент, использованный при чек-ине.
     *
     * @return BelongsTo<MembershipPurchase, $this>
     */
    public function membershipPurchase(): BelongsTo
    {
        return $this->belongsTo(MembershipPurchase::class);
    }
}
