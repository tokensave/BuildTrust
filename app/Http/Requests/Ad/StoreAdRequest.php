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

    protected function prepareForValidation(): void
    {
        if ($this->input('status') === null) {
            $this->merge([
                'status' => AdsStatusEnum::DRAFT->value,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'nullable|numeric|min:0',
            'status'      => ['nullable', Rule::enum(AdsStatusEnum::class)],
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ];
    }
}
