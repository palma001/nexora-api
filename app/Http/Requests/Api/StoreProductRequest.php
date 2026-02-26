<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'stock' => 'nullable|integer',
            'stock_min' => 'nullable|integer',
            'image' => 'nullable|image|max:2048', // 2MB max
        ];
    }
}
