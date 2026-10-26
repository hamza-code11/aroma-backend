<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tax_id' => ['required', 'string', 'max:100'],
        ];
    }
}
