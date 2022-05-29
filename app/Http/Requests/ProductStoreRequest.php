<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => ['required'],
            'sku' => ['required', 'unique:products,sku'],
            'description' => ['nullable'],
            'product_image' => ['nullable'],
            'product_variant' => ['nullable'],
            'product_variant_prices' => ['nullable'],
        ];
    }
}
