<?php

namespace App\Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс пользователя (владельца).
 *
 * Используется для форматирования данных пользователя в API-ответах.
 *
 * @mixin \App\Models\User
 *
 * @property int $id ID пользователя
 * @property string $name Имя пользователя
 * @property string $email Email пользователя
 */
class UserResource extends JsonResource
{
    /**
     * Преобразование User в JSON-формат.
     *
     * @return array{
     *   id: int,
     *   name: string,
     *   email: string,
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
        ];
    }
}
