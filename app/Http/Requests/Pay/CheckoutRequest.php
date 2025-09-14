<?php

namespace App\Http\Requests\Pay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', Rule::exists('profiles', 'slug')],
            'amount_cents' => ['required', 'integer', 'min:100', 'max:10000000'],
            'currency' => ['sometimes', 'string', 'in:eur'],
        ];
    }
}
