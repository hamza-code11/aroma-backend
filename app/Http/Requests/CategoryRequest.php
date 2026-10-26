<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Agar admin check lagana ho toh yahan laga sakte hain
    }

    public function rules(): array
    {
        // Update ke waqt current ID ko ignore karna zaroori hai slug uniqueness ke liye
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $categoryId,
            'is_active' => 'boolean',
        ];
    }
}