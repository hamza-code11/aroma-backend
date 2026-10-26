<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product  = $this->route('product');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'name'           => ['required', 'string', 'min:2', 'max:255'],
            'slug'           => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'sku'            => [
                'required', 'string', 'max:100',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'description'    => ['nullable', 'string', 'max:1000'],
            'regular_price'  => ['required', 'numeric', 'gt:0'],
            'sale_price'     => ['nullable', 'numeric', 'min:0', 'lt:regular_price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'weight'         => ['required', 'numeric', 'gt:0'],
            'weight_unit'    => ['required', Rule::in(['kg', 'g', 'lb', 'oz'])],
            'length'         => ['nullable', 'numeric', 'min:0'],
            'width'          => ['nullable', 'numeric', 'min:0'],
            'height'         => ['nullable', 'numeric', 'min:0'],
            'dimension_unit' => ['required', Rule::in(['cm', 'mm', 'in'])],
            'shipping_class' => ['nullable', 'string', 'max:100'],
            'is_active'      => ['nullable', 'boolean'],

            // Create pe required, update pe sirf tab validate hoga jab bheji jaye
            'main_image'     => [$isUpdate ? 'sometimes' : 'required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'images'         => ['sometimes', 'array'],
            'images.*'       => ['image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }
}