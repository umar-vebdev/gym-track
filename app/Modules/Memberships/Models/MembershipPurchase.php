<?php

namespace App\Modules\Memberships\Models;

use App\Modules\Clients\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель проданного абонемента (покупки клиента).
 *
 * @property int            $id
 * @property int            $client_id          ID клиента
 * @property int            $membership_type_id ID типа абонемента
 * @property float          $amount_paid        Фактически уплаченная сумма
 * @property \Carbon\Carbon $starts_at          Дата начала
 * @property \Carbon\Carbon|null $expires_at    Дата окончания (null если по визитам)
 * @property int|null       $visits_left        Остаток визитов (null если по дням)
 * @property string|null    $payment_method     Способ оплаты (cash, card, transfer)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Client $client
 * @property-read MembershipType $membershipType
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Visits\Models\Visit[] $visits
 */
class MembershipPurchase extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'membership_type_id',
        'amount_paid',
        'starts_at',
        'expires_at',
        'visits_left',
        'payment_method',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id'          => 'integer',
            'membership_type_id' => 'integer',
            'amount_paid'        => 'float',
            'starts_at'          => 'date',
            'expires_at'         => 'date',
            'visits_left'        => 'integer',
        ];
    }

    /**
     * Клиент, купивший абонемент.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Тарифный план (тип абонемента).
     *
     * @return BelongsTo<MembershipType, $this>
     */
    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    /**
     * Визиты по этому абонементу.
     *
     * @return HasMany<\App\Modules\Visits\Models\Visit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(\App\Modules\Visits\Models\Visit::class);
    }

    /**
     * Проверка, активен ли абонемент на текущий момент.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $today = now()->startOfDay();

        if ($this->starts_at->isAfter($today)) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isBefore($today)) {
            return false;
        }

        if ($this->visits_left !== null && $this->visits_left <= 0) {
            return false;
        }

        return true;
    }
}
