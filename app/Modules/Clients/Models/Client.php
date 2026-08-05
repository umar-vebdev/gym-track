<?php

namespace App\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель клиента (посетителя зала).
 *
 * Клиент — это запись в базе, замена строки в тетради.
 * Клиенты не являются пользователями приложения (см. ТЗ, раздел 2).
 *
 * @property int            $id
 * @property string         $client_code  Авто-код клиента (GT-0001)
 * @property string         $full_name    ФИО клиента
 * @property string         $phone        Телефон (уникальный идентификатор)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Memberships\Models\MembershipPurchase[] $membershipPurchases
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Visits\Models\Visit[] $visits
 */
class Client extends Model
{
    use HasFactory;

    /**
     * Создать новый экземпляр фабрики для модели.
     */
    protected static function newFactory(): \Database\Factories\ClientFactory
    {
        return \Database\Factories\ClientFactory::new();
    }

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'client_code',
        'full_name',
        'phone',
    ];

    /**
     * Авто-генерация client_code при создании.
     *
     * Формат: GT-XXXX (например GT-0001, GT-0042).
     */
    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->client_code)) {
                $client->client_code = static::generateCode();
            }
        });
    }

    /**
     * Генерация следующего кода клиента.
     *
     * @return string Код в формате GT-XXXX
     */
    public static function generateCode(): string
    {
        $last = static::query()
            ->orderByDesc('id')
            ->value('client_code');

        if ($last && preg_match('/GT-(\d+)/', $last, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return 'GT-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Покупки абонементов клиента.
     *
     * @return HasMany<\App\Modules\Memberships\Models\MembershipPurchase, $this>
     */
    public function membershipPurchases(): HasMany
    {
        return $this->hasMany(\App\Modules\Memberships\Models\MembershipPurchase::class);
    }

    /**
     * Визиты клиента.
     *
     * @return HasMany<\App\Modules\Visits\Models\Visit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(\App\Modules\Visits\Models\Visit::class);
    }

    /**
     * Покупки товаров.
     *
     * @return HasMany<\App\Modules\Products\Models\ProductSale, $this>
     */
    public function productSales(): HasMany
    {
        return $this->hasMany(\App\Modules\Products\Models\ProductSale::class);
    }
}
