<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'customer_type' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(Customer::STATUSES)],
            'notes' => ['nullable', 'string'],
            'discount_amount' => ['prohibited'],
        ];

        if ($this->user()?->hasRole(User::ROLE_ADMIN)) {
            $rules['product_discounts'] = ['nullable', 'array'];
            $rules['product_discounts.*.product_id'] = ['required', Rule::exists('tenant.products', 'id')];
            $rules['product_discounts.*.store_id'] = ['required', Rule::exists('tenant.stores', 'id')];
            $rules['product_discounts.*.discount_amount'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['product_discounts'] = ['prohibited'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $productDiscounts = collect($this->input('product_discounts', []))
            ->map(function ($discount) {
                return [
                    'product_id' => $discount['product_id'] ?? null,
                    'store_id' => $discount['store_id'] ?? null,
                    'discount_amount' => $discount['discount_amount'] ?? null,
                    'enabled' => filter_var($discount['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(fn ($discount) => $discount['enabled'] && filled($discount['product_id']) && filled($discount['store_id']) && filled($discount['discount_amount']))
            ->map(fn ($discount) => [
                'product_id' => $discount['product_id'],
                'store_id' => $discount['store_id'],
                'discount_amount' => $discount['discount_amount'],
            ])
            ->values()
            ->all();

        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : null,
            'product_discounts' => $productDiscounts,
        ]);
    }
}
