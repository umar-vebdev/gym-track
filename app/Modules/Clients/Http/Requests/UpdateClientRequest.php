<?php

namespace App\Modules\Clients\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Запрос на обновление данных клиента.
 *
 * @body string $full_name ФИО клиента. Example: Петров Пётр Петрович
 * @body string $phone Телефон клиента. Example: +7 999 987-65-43
 */
class UpdateClientRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $clientId = $this->route('client');

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone'     => ['sometimes', 'string', 'max:20', Rule::unique('clients', 'phone')->ignore($clientId)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.max' => 'ФИО не должно превышать 255 символов',
            'phone.max'     => 'Телефон не должен превышать 20 символов',
            'phone.unique'  => 'Клиент с таким телефоном уже существует',
        ];
    }
}
