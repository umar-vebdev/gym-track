<?php

namespace App\Modules\Visits\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на регистрацию визита (чек-ин).
 *
 * @body int $client_id required ID клиента. Example: 1
 * @body int $membership_purchase_id ID конкретного абонемента (опционально, если не передано — подбирается автоматически). Example: 1
 */
class StoreVisitRequest extends FormRequest
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
            'client_id'              => ['required', 'integer', 'exists:clients,id'],
            'membership_purchase_id' => ['sometimes', 'nullable', 'integer', 'exists:membership_purchases,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required'          => 'Выберите клиента для чек-ина',
            'client_id.exists'            => 'Указанный клиент не найден',
            'membership_purchase_id.exists' => 'Указанный абонемент не найден',
        ];
    }
}
