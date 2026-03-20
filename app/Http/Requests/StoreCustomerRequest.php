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
        ];

        $rules['discount_amount'] = $this->user()?->hasRole(User::ROLE_ADMIN)
            ? ['nullable', 'integer', Rule::in(Customer::DISCOUNT_AMOUNTS)]
            : ['prohibited'];

        return $rules;
    }
}
