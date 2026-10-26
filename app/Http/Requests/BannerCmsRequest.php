<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerCmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // YEH METHOD ADD KAREIN
    protected function prepareForValidation()
    {
        if ($this->has('cards') && is_string($this->cards)) {
            $this->merge([
                'cards' => json_decode($this->cards, true),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cards' => ['required', 'array'],
            'cards.*.id' => ['required', 'string'],
            'cards.*.label' => ['required', 'string', 'max:255'],
            'cards.*.title' => ['required', 'string', 'max:255'],
            'cards.*.description' => ['required', 'string', 'max:1000'],
        ];
    }
}