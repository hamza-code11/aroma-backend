<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactCmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ✅ YEH NAYA METHOD ADD KAREIN (FormData JSON strings ko array mein convert karne ke liye)
    protected function prepareForValidation()
    {
        $jsonFields = ['hero', 'info', 'faq'];
        
        foreach ($jsonFields as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([
                    $field => json_decode($this->$field, true),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'hero.eyebrow'        => ['required', 'string', 'max:255'],
            'hero.title'          => ['required', 'string', 'max:255'],
            'hero.titleHighlight' => ['required', 'string', 'max:255'],
            'hero.lead'           => ['required', 'string', 'max:1000'],

            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'info' => ['required', 'array'],
            'info.*.id' => ['required', 'string'],
            'info.*.label' => ['required', 'string', 'max:255'],
            'info.*.value' => ['required', 'string', 'max:255'],
            'info.*.icon'  => ['required', 'string', 'in:mail,phone,map,clock'],

            'faq.eyebrow' => ['required', 'string', 'max:255'],
            'faq.title'   => ['required', 'string', 'max:255'],
            'faq.items'   => ['required', 'array'],
            'faq.items.*.id' => ['required', 'string'],
            'faq.items.*.question' => ['required', 'string', 'max:500'],
            'faq.items.*.answer'   => ['required', 'string', 'max:2000'],
        ];
    }
}
