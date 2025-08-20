<?php

namespace App\Enums\AdEnums;

enum AdFeaturesEnum: string
{
    // Материалы
    case FROST_RESISTANT = 'frost_resistant';
    case DURABLE = 'durable';
    case LIGHTWEIGHT = 'lightweight';
    case LOW_PRICE = 'low_price';
    case ECO_FRIENDLY = 'eco_friendly';
    case LONG_LASTING = 'long_lasting';
    
    // Инструменты
    case POWERFUL = 'powerful';
    case RELIABLE = 'reliable';
    case COMPACT = 'compact';
    case WARRANTY = 'warranty';
    case WIRELESS = 'wireless';
    
    // Оборудование
    case HIGH_PERFORMANCE = 'high_performance';
    case ECONOMICAL = 'economical';
    case MOBILE = 'mobile';
    case AUTOMATED = 'automated';
    
    // Строительство
    case EXPERIENCED = 'experienced';
    case QUALITY = 'quality';
    case URGENT = 'urgent';
    case LICENSED = 'licensed';
    case INSURED = 'insured';
    
    // Ремонт
    case FAST = 'fast';
    case AFFORDABLE = 'affordable';
    case MATERIALS_INCLUDED = 'materials_included';
    case HOME_VISIT = 'home_visit';
    
    // Дизайн
    case CREATIVE = 'creative';
    case PROFESSIONAL = 'professional';
    case INNOVATIVE = 'innovative';
    case INDIVIDUAL_APPROACH = 'individual_approach';
    case VISUALIZATION_3D = 'visualization_3d';

    public function label(): string
    {
        return match ($this) {
            // Материалы
            self::FROST_RESISTANT => 'Морозостойкость',
            self::DURABLE => 'Прочность',
            self::LIGHTWEIGHT => 'Легкость',
            self::LOW_PRICE => 'Низкая цена',
            self::ECO_FRIENDLY => 'Экологичность',
            self::LONG_LASTING => 'Долговечность',
            
            // Инструменты
            self::POWERFUL => 'Мощность',
            self::RELIABLE => 'Надежность',
            self::COMPACT => 'Компактность',
            self::WARRANTY => 'Гарантия',
            self::WIRELESS => 'Беспроводной',
            
            // Оборудование
            self::HIGH_PERFORMANCE => 'Производительность',
            self::ECONOMICAL => 'Экономичность',
            self::MOBILE => 'Мобильность',
            self::AUTOMATED => 'Автоматизация',
            
            // Строительство
            self::EXPERIENCED => 'Опыт',
            self::QUALITY => 'Качество',
            self::URGENT => 'Срочность',
            self::LICENSED => 'Лицензия',
            self::INSURED => 'Страховка',
            
            // Ремонт
            self::FAST => 'Скорость',
            self::AFFORDABLE => 'Недорого',
            self::MATERIALS_INCLUDED => 'Материалы в комплекте',
            self::HOME_VISIT => 'Выезд на дом',
            
            // Дизайн
            self::CREATIVE => 'Креативность',
            self::PROFESSIONAL => 'Профессионализм',
            self::INNOVATIVE => 'Инновации',
            self::INDIVIDUAL_APPROACH => 'Индивидуальный подход',
            self::VISUALIZATION_3D => '3D визуализация',
        };
    }

    /**
     * Получить характеристики для конкретной категории
     */
    public static function getByCategory(AdCategoryEnum $category): array
    {
        return match ($category) {
            AdCategoryEnum::MATERIALS => [
                self::FROST_RESISTANT,
                self::DURABLE,
                self::LIGHTWEIGHT,
                self::LOW_PRICE,
                self::ECO_FRIENDLY,
                self::LONG_LASTING,
            ],
            AdCategoryEnum::TOOLS => [
                self::POWERFUL,
                self::RELIABLE,
                self::LIGHTWEIGHT,
                self::COMPACT,
                self::WARRANTY,
                self::WIRELESS,
            ],
            AdCategoryEnum::EQUIPMENT => [
                self::HIGH_PERFORMANCE,
                self::RELIABLE,
                self::ECONOMICAL,
                self::MOBILE,
                self::AUTOMATED,
            ],
            AdCategoryEnum::CONSTRUCTION => [
                self::EXPERIENCED,
                self::QUALITY,
                self::URGENT,
                self::WARRANTY,
                self::LICENSED,
                self::INSURED,
            ],
            AdCategoryEnum::REPAIR => [
                self::QUALITY,
                self::FAST,
                self::AFFORDABLE,
                self::EXPERIENCED,
                self::MATERIALS_INCLUDED,
                self::HOME_VISIT,
            ],
            AdCategoryEnum::DESIGN => [
                self::CREATIVE,
                self::PROFESSIONAL,
                self::INNOVATIVE,
                self::INDIVIDUAL_APPROACH,
                self::VISUALIZATION_3D,
            ],
        };
    }

    /**
     * Получить массив для использования в API
     */
    public static function toArrayByCategory(AdCategoryEnum $category): array
    {
        $features = self::getByCategory($category);
        
        return collect($features)->mapWithKeys(fn($feature) => [
            $feature->value => [
                'value' => $feature->value,
                'label' => $feature->label(),
            ]
        ])->toArray();
    }
}
