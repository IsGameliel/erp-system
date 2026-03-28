<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => array_filter([
                'required',
                'string',
                'max:255',
                ProductCategory::schemaIsReady() ? Rule::unique('tenant.product_categories', 'name') : null,
            ]),
            'description' => ['nullable', 'string'],
        ];
    }
}
