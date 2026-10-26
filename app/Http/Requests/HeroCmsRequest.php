<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeroCmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            
            'primary_btn_label' => ['nullable', 'string', 'max:100'],
            'primary_btn_link' => ['nullable', 'string', 'max:255'],
            
            'secondary_btn_label' => ['nullable', 'string', 'max:100'],
            'secondary_btn_link' => ['nullable', 'string', 'max:255'],
        ];
    }
}
