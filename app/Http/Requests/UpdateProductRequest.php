<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('tenant.products', 'sku')->ignore($product?->id)],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', Rule::exists('tenant.product_categories', 'id')],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Product::STATUSES)],
            'store_quantities' => ['nullable', 'array'],
            'store_quantities.*.store_id' => ['required', Rule::exists(Store::class, 'id')],
            'store_quantities.*.quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
