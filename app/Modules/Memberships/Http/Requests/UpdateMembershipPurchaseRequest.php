<?php

namespace App\Modules\Memberships\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'visits_left' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,transfer,other'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
