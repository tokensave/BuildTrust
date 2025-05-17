<?php

namespace App\Http\Requests\Ad;

use App\Enums\AdEnums\AdsStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'nullable|numeric|min:0',
            'status'      => ['required', Rule::enum(AdsStatusEnum::class)],
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ];
    }
}
