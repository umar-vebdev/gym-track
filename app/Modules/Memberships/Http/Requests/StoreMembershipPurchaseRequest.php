<?php

namespace App\Modules\Memberships\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на продажу абонемента клиенту.
 *
 * @body int $client_id required ID клиента. Example: 1
 * @body int $membership_type_id required ID типа абонемента. Example: 1
 * @body float $amount_paid Фактическая сумма оплаты (если не передана, берется базовая цена). Example: 2500.00
 * @body string $starts_at Дата начала действия в формате YYYY-MM-DD (по умолчанию сегодня). Example: 2026-08-01
 * @body string $payment_method Способ оплаты (cash, card, transfer). Example: cash
 */
class StoreMembershipPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'client_id'          => ['required', 'integer', 'exists:clients,id'],
            'membership_type_id' => ['required', 'integer', 'exists:membership_types,id'],
            'amount_paid'        => ['sometimes', 'numeric', 'min:0'],
            'starts_at'          => ['sometimes', 'date'],
            'payment_method'     => ['sometimes', 'string', 'in:cash,card,transfer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'          => 'Выберите клиента',
            'client_id.exists'            => 'Указанный клиент не найден',
            'membership_type_id.required' => 'Выберите тип абонемента',
            'membership_type_id.exists'   => 'Указанный тип абонемента не найден',
            'amount_paid.numeric'         => 'Сумма оплаты должна быть числом',
            'starts_at.date'              => 'Некорректная дата начала',
            'payment_method.in'           => 'Способ оплаты должен быть: cash, card или transfer',
        ];
    }
}
