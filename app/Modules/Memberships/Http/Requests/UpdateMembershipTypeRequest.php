<?php

namespace App\Modules\Memberships\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на обновление типа абонемента.
 *
 * @body string $name Название тарифа. Example: Месячный безлимит
 * @body string $duration_type Тип длительности ('days' или 'visits'). Example: days
 * @body int $duration_value Значение длительности. Example: 30
 * @body float $price Базовая цена. Example: 2700.00
 * @body bool $is_active Статус активности. Example: true
 */
class UpdateMembershipTypeRequest extends FormRequest
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
            'name'           => ['sometimes', 'string', 'max:255'],
            'duration_type'  => ['sometimes', 'string', 'in:days,visits'],
            'duration_value' => ['sometimes', 'integer', 'min:1'],
            'price'          => ['sometimes', 'numeric', 'min:0'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'duration_type.in'   => 'Тип длительности должен быть days или visits',
            'duration_value.min' => 'Количество должно быть не менее 1',
            'price.numeric'      => 'Цена должна быть числом',
            'price.min'          => 'Цена не может быть отрицательной',
        ];
    }
}
