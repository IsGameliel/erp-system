<?php

namespace App\Http\Requests;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateSalesOrderRequest extends StoreSalesOrderRequest
{
    public function rules(): array
    {
        if ($this->user()?->hasRole(User::ROLE_SALES_OFFICER)) {
            return [
                'status' => ['required', Rule::in(SalesOrder::STATUSES)],
                'payment_status' => ['required', Rule::in(SalesOrder::PAYMENT_STATUSES)],
                'payment_method' => ['nullable', Rule::in(SalesOrder::PAYMENT_METHODS)],
            ];
        }

        return parent::rules();
    }

    public function withValidator($validator): void
    {
        if (! $this->user()?->hasRole(User::ROLE_SALES_OFFICER)) {
            parent::withValidator($validator);

            return;
        }

        $validator->after(function ($validator): void {
            if ($this->input('payment_status') === SalesOrder::PAYMENT_STATUS_PAID && ! $this->filled('payment_method')) {
                $validator->errors()->add('payment_method', 'Choose a payment method for paid orders.');
            }
        });
    }
}
