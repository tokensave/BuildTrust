<?php

namespace App\Http\Requests\Message;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $adId = $this->route('ad') instanceof Ad ? $this->route('ad')->id : (int) $this->route('ad');
        $recipientId = $this->route('recipient') instanceof User ? $this->route('recipient')->id : (int) $this->route('recipient');

        $this->merge([
            'author_id' => auth()->id(),
            'ad_id' => $adId,
            'recipient_id' => $recipientId,
        ]);
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'integer', 'exists:users,id'],
            'ad_id' => ['required', 'integer', 'exists:ads,id'],
            'recipient_id' => ['required', 'integer', 'exists:users,id']
        ];
    }
}
