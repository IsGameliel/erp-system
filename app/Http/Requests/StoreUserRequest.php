<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN]) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'role' => ['required', Rule::in(User::ROLES)],
            'organization_id' => ['nullable', 'integer', Rule::exists(Organization::class, 'id')],
            'access_enabled' => ['nullable', 'boolean'],
            'access_expires_at' => ['nullable', 'date'],
            'store_id' => ['nullable', 'integer', Rule::exists(Store::class, 'id')],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (in_array($this->input('role'), [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true)) {
            $this->merge(['store_id' => null]);
        }

        $this->merge([
            'organization_id' => $this->user()?->isSuperAdmin() ? $this->input('organization_id') : $this->user()?->organization_id,
            'access_enabled' => $this->boolean('access_enabled', false),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = $this->input('role');
            $storeId = $this->input('store_id');

            if ($storeId && ! in_array($role, [User::ROLE_SALES_OFFICER, User::ROLE_PROCUREMENT_OFFICER], true)) {
                $validator->errors()->add('store_id', 'Only sales and procurement officers can be assigned to a store.');
            }

            if (! $this->user()?->isSuperAdmin() && $role === User::ROLE_SUPER_ADMIN) {
                $validator->errors()->add('role', 'Only the super admin can create another super admin.');
            }
        });
    }
}
