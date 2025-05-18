<?php

namespace App\Http\Requests\Ad;

use App\Enums\AdEnums\AdsStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'status'      => ['required', Rule::enum(AdsStatusEnum::class)],
            'images.*'    => ['nullable', 'image', 'max:2048'], // по 2МБ на файл
        ];
    }
}
