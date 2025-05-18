<?php

declare(strict_types=1);
declare(ticks=1000);


return [
    'required' => 'Поле «:attribute» обязательно для заполнения.',
    'string' => 'Поле «:attribute» должно быть строкой.',
    'numeric' => 'Поле «:attribute» должно быть числом.',
    'min' => [
        'numeric' => 'Поле «:attribute» должно быть не меньше :min.',
        'string' => 'Поле «:attribute» должно содержать не менее :min символов.',
    ],
    'max' => [
        'string' => 'Поле «:attribute» не должно превышать :max символов.',
    ],
    'image' => 'Поле «:attribute» должно быть изображением.',
    'mimes' => 'Поле «:attribute» должно быть файлом одного из следующих типов: :values.',
    'enum' => 'Выбранное значение для поля «:attribute» недопустимо.',

    'attributes' => [
        'title' => 'заголовок',
        'description' => 'описание',
        'price' => 'цена',
        'status' => 'статус',
        'images' => 'изображения',
        'images.*' => 'изображение',
    ],
];
