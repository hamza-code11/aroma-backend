<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'ratings' => ['required', 'integer', 'between:1,5'],
            'reviews' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
