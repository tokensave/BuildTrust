<?php

namespace App\Http\Requests\Deal;

use App\Domain\Deal\ValueObjects\DealStatus;
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
            'status' => ['required', Rule::enum(DealStatus::class)],
        ];
    }
}
