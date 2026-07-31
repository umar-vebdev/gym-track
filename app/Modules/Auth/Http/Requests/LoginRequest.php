<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Запрос на вход в систему.
 *
 * @body string $email required Email владельца. Example: owner@gymtrack.local
 * @body string $password required Пароль. Example: password
 * @body string $device_name Имя устройства для токена (опционально). Example: admin-tablet
 */
class LoginRequest extends FormRequest
{
    /**
     * Авторизация запроса — логин доступен всем (гостям).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email'       => ['required', 'string', 'email'],
            'password'    => ['required', 'string', 'min:6'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'Email обязателен',
            'email.email'       => 'Некорректный формат email',
            'password.required' => 'Пароль обязателен',
            'password.min'      => 'Пароль должен быть не менее 6 символов',
        ];
    }
}
