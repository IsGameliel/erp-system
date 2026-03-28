<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends StoreProductCategoryRequest
{
    public function rules(): array
    {
        $category = $this->route('product_category');

        return [
            'name' => array_filter([
                'required',
                'string',
                'max:255',
                ProductCategory::schemaIsReady() ? Rule::unique('tenant.product_categories', 'name')->ignore($category?->id) : null,
            ]),
            'description' => ['nullable', 'string'],
        ];
    }
}
