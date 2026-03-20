<?php

namespace App\Http\Requests;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) ?? false;
    }

    public function rules(): array
    {
        /** @var \App\Models\User $managedUser */
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($managedUser->id),
            ],
            'role' => ['required', Rule::in(User::ROLES)],
            'store_id' => ['nullable', 'integer', Rule::exists(Store::class, 'id')],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('role') === User::ROLE_ADMIN) {
            $this->merge(['store_id' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = $this->input('role');
            $storeId = $this->input('store_id');

            if ($storeId && ! in_array($role, [User::ROLE_SALES_OFFICER, User::ROLE_PROCUREMENT_OFFICER], true)) {
                $validator->errors()->add('store_id', 'Only sales and procurement officers can be assigned to a store.');
            }
        });
    }
}
