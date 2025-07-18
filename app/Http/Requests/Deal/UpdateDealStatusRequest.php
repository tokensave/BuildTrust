<?php

namespace App\Http\Requests\Deal;

use App\Enums\DealEnums\DealStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDealStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DealStatusEnum::class)],
        ];
    }
}
