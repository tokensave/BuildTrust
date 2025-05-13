<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'username'     => 'nullable|string|max:255|unique:users,username,' . $userId,
            'email'        => 'required|email|max:255|unique:users,email,' . $userId,
            'company_name' => 'nullable|string|max:255',
            'inn'          => 'nullable|string|max:20',
            'phone'        => 'nullable|string|max:20',
            'city'         => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:255',
            'website'      => 'nullable|url|max:255',
            'verified'     => 'nullable|boolean',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ];
    }
}
