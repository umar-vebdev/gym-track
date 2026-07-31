<?php

namespace App\Modules\Clients\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на создание клиента.
 *
 * @body string $full_name required ФИО клиента. Example: Иванов Иван Иванович
 * @body string $phone required Телефон клиента (уникальный). Example: +7 999 123-45-67
 */
class StoreClientRequest extends FormRequest
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
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'string', 'max:20', 'unique:clients,phone'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'ФИО обязательно для заполнения',
            'full_name.max'      => 'ФИО не должно превышать 255 символов',
            'phone.required'     => 'Телефон обязателен',
            'phone.max'          => 'Телефон не должен превышать 20 символов',
            'phone.unique'       => 'Клиент с таким телефоном уже существует',
        ];
    }
}
