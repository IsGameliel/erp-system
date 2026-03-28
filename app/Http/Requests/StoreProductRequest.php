<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('tenant.products', 'sku')],
            'description' => ['nullable', 'string'],
            'category_id' => array_filter([
                'nullable',
                ProductCategory::schemaIsReady() ? Rule::exists('tenant.product_categories', 'id') : null,
            ]),
            'selling_price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(Product::STATUSES)],
            'store_quantities' => ['nullable', 'array'],
            'store_quantities.*.store_id' => ['required', Rule::exists(Store::class, 'id')],
            'store_quantities.*.quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $storeQuantities = collect($this->input('store_quantities', []))
            ->map(fn ($entry, $storeId) => [
                'store_id' => $entry['store_id'] ?? $storeId,
                'quantity' => $entry['quantity'] ?? 0,
            ])
            ->filter(fn ($entry) => filled($entry['store_id']))
            ->values()
            ->all();

        $this->merge([
            'category_id' => $this->filled('category_id') && ProductCategory::schemaIsReady() ? $this->input('category_id') : null,
            'store_quantities' => $storeQuantities,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $allocatedQuantity = collect($this->input('store_quantities', []))
                ->sum(fn ($entry) => (int) ($entry['quantity'] ?? 0));

            if ($allocatedQuantity > (int) $this->input('stock_quantity', 0)) {
                $validator->errors()->add('store_quantities', 'Assigned store quantity cannot exceed total stock quantity.');
            }
        });
    }
}
