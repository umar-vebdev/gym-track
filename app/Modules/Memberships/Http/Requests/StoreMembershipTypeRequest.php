<?php

namespace App\Modules\Memberships\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на создание типа абонемента.
 *
 * @body string $name required Название тарифа. Example: Месячный безлимит
 * @body string $duration_type required Тип длительности ('days' или 'visits'). Example: days
 * @body int $duration_value required Значение длительности (дни или визиты). Example: 30
 * @body float $price required Базовая цена. Example: 2500.00
 * @body bool $is_active Активен ли тариф. Example: true
 */
class StoreMembershipTypeRequest extends FormRequest
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
            'name'           => ['required', 'string', 'max:255'],
            'duration_type'  => ['required', 'string', 'in:days,visits'],
            'duration_value' => ['required', 'integer', 'min:1'],
            'price'          => ['required', 'numeric', 'min:0'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'           => 'Название тарифа обязательно',
            'duration_type.required'  => 'Укажите тип длительности (days или visits)',
            'duration_type.in'        => 'Тип длительности должен быть days или visits',
            'duration_value.required' => 'Укажите количество дней или визитов',
            'duration_value.min'      => 'Количество должно быть не менее 1',
            'price.required'          => 'Укажите цену тарифа',
            'price.numeric'           => 'Цена должна быть числом',
            'price.min'               => 'Цена не может быть отрицательной',
        ];
    }
}
