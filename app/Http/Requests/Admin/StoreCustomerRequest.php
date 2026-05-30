<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_blocked' => $this->boolean('is_blocked')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                // Link to a user that holds the customer role…
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->whereIn(
                        'role_id',
                        fn ($sub) => $sub->select('id')->from('roles')->where('slug', RoleEnum::Customer->value)
                    )
                ),
                // …and that does not already own a customer profile.
                Rule::unique('customers', 'user_id'),
            ],
            'company_name' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'is_blocked' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.unique' => __('That user already has a customer profile.'),
            'user_id.exists' => __('Select a valid user with the customer role.'),
        ];
    }
}

