<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        $profileId = $this->route('user')?->profile?->id;

        return [
            'display_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'review_url' => ['nullable', 'url', 'max:255'],
            'slug' => [
                'required', 'string', 'lowercase', 'min:3', 'max:50', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('profiles', 'slug')->ignore($profileId),
            ],
            'quick_amounts' => ['required', 'array', 'size:4'],
            'quick_amounts.*' => ['required', 'integer', 'min:1'],
        ];
    }
}

