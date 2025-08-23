<?php

namespace App\Http\Requests\Deal;

use App\Domain\Deal\ValueObjects\DealStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->input('status') === null) {
            $this->merge([
                'status' => DealStatus::PENDING->value,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(DealStatus::class)],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
