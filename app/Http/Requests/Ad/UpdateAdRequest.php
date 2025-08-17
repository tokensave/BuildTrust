<?php

namespace App\Http\Requests\Ad;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
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
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(AdTypeEnum::class)],
            'category' => ['nullable', Rule::enum(AdCategoryEnum::class)],
            'subcategory' => [
                'nullable', 
                Rule::enum(AdSubcategoryEnum::class),
                function ($attribute, $value, $fail) {
                    if ($value && $this->input('category')) {
                        $categoryEnum = AdCategoryEnum::from($this->input('category'));
                        $subcategoryEnum = AdSubcategoryEnum::from($value);
                        
                        if ($subcategoryEnum->getCategory() !== $categoryEnum) {
                            $fail('Подкатегория не соответствует выбранной категории.');
                        }
                    }
                },
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_urgent' => ['boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'status' => ['required', Rule::enum(AdsStatusEnum::class)],
            'images.*' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Выберите тип объявления',
            'category.required' => 'Выберите категорию',
            'subcategory.exists' => 'Выбранная подкатегория недоступна',
            'title.required' => 'Заголовок обязателен для заполнения',
            'title.max' => 'Заголовок не должен превышать 255 символов',
            'description.required' => 'Описание обязательно для заполнения',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'location.max' => 'Местоположение не должно превышать 255 символов',
            'images.*.image' => 'Файл должен быть изображением',
            'images.*.max' => 'Размер изображения не должен превышать 2MB',
        ];
    }
}
