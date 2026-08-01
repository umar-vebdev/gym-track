<?php

namespace App\Modules\Memberships\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель типа абонемента (тарифного плана).
 *
 * @property int            $id
 * @property string         $name           Название ("Месячный", "12 визитов")
 * @property string         $duration_type  Тип длительности ('days' или 'visits')
 * @property int            $duration_value Значение (30 дней или 12 визитов)
 * @property float          $price          Базовая цена
 * @property bool           $is_active      Доступен ли для новых продаж
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|MembershipPurchase[] $purchases
 */
class MembershipType extends Model
{
    use HasFactory;

    /**
     * Создать новый экземпляр фабрики для модели.
     */
    protected static function newFactory(): \Database\Factories\MembershipTypeFactory
    {
        return \Database\Factories\MembershipTypeFactory::new();
    }

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'duration_type',
        'duration_value',
        'price',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_value' => 'integer',
            'price'          => 'float',
            'is_active'      => 'boolean',
        ];
    }

    /**
     * Продажи данного типа абонемента.
     *
     * @return HasMany<MembershipPurchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(MembershipPurchase::class);
    }
}
