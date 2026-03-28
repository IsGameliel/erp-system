<?php

namespace App\Http\Requests;

use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'customer_mode' => ['nullable', Rule::in(['existing', 'new'])],
            'customer_id' => ['nullable', Rule::exists('tenant.customers', 'id')],
            'store_id' => ['nullable', 'integer', Rule::exists(Store::class, 'id')],
            'order_date' => ['required', 'date'],
            'status' => ['required', Rule::in(SalesOrder::STATUSES)],
            'payment_status' => ['required', Rule::in(SalesOrder::PAYMENT_STATUSES)],
            'payment_method' => ['nullable', Rule::in(SalesOrder::PAYMENT_METHODS)],
            'due_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'customer.full_name' => ['nullable', 'string', 'max:255'],
            'customer.business_name' => ['nullable', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'customer.address' => ['nullable', 'string'],
            'customer.customer_type' => ['nullable', 'string', 'max:100'],
            'customer.notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('tenant.products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];

        $rules['discount'] = $this->user()?->hasRole(User::ROLE_ADMIN)
            ? ['nullable', 'numeric', 'min:0']
            : ['prohibited'];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('customer_mode')) {
            $this->merge([
                'customer_mode' => blank($this->input('customer.full_name')) ? 'existing' : 'new',
            ]);
        }

        if (! $this->filled('payment_status')) {
            $this->merge([
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
            ]);
        }

        if ($this->input('payment_status') === SalesOrder::PAYMENT_STATUS_PENDING) {
            $this->merge([
                'payment_method' => null,
                'amount_paid' => 0,
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('customer_mode') === 'new') {
                if (blank($this->input('customer.full_name'))) {
                    $validator->errors()->add('customer.full_name', 'Enter the new customer name before checkout.');
                }
            } elseif (! $this->filled('customer_id')) {
                $validator->errors()->add('customer_id', 'Select an existing customer before checkout.');
            }

            if ($this->input('payment_status') === SalesOrder::PAYMENT_STATUS_PAID && ! $this->filled('payment_method')) {
                $validator->errors()->add('payment_method', 'Choose a payment method for paid orders.');
            }

            if ($this->input('payment_status') === SalesOrder::PAYMENT_STATUS_PENDING && ! $this->filled('due_date')) {
                $validator->errors()->add('due_date', 'Add a due date for credit sales.');
            }

            if ($this->user()?->hasRole(User::ROLE_SALES_OFFICER) && ! $this->user()->store_id && ! $this->filled('store_id')) {
                $validator->errors()->add('store_id', 'Assign this sales officer to a store before checking out sales.');
            }
        });
    }
}
