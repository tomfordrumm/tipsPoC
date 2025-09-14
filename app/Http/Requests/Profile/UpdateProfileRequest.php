<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('slug') && is_string($this->input('slug'))) {
            $this->merge([
                'slug' => strtolower($this->input('slug')),
            ]);
        }
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $profile = $this->user()->profile;

        return [
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'display_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'review_url' => ['nullable', 'url', 'max:255'],
            'slug' => [
                'required',
                'string',
                'lowercase',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('profiles', 'slug')->ignore(optional($profile)->id),
            ],
            'quick_amounts' => ['required', 'array', 'size:4'],
            'quick_amounts.*' => ['required', 'integer', 'min:1'],
        ];
    }
}
