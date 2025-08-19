<?php

namespace Database\Factories;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ad>
 */
class AdFactory extends Factory
{
    protected $model = Ad::class;
    
    public function definition(): array
    {
        $type = $this->faker->randomElement(AdTypeEnum::cases());
        $categories = AdCategoryEnum::getByType($type);
        $category = $this->faker->randomElement($categories);
        
        // Получаем подкатегории (если есть)
        $subcategory = $this->faker->optional(0.8)->randomElement(
            $this->getSubcategoriesForCategory($category)
        );
        
        $title = $this->generateTitleByType($type, $category);
        
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'type' => $type,
            'category' => $category,
            'subcategory' => $subcategory,
            'location' => $this->faker->city(),
            'description' => $this->generateDescriptionByType($type, $category),
            'price' => $type === AdTypeEnum::SERVICES 
                ? $this->faker->optional(0.7)->randomFloat(2, 1000, 100000) // Услуги могут быть без цены
                : $this->faker->randomFloat(2, 100, 50000), // Товары обычно с ценой
            'slug' => Str::slug($title) . '-' . uniqid(),
            'status' => $this->faker->randomElement([
                AdsStatusEnum::PUBLISHED, // 70% опубликованных
                AdsStatusEnum::PUBLISHED,
                AdsStatusEnum::PUBLISHED,
                AdsStatusEnum::PUBLISHED,
                AdsStatusEnum::PUBLISHED,
                AdsStatusEnum::PUBLISHED,
                AdsStatusEnum::PUBLISHED,
                AdsStatusEnum::DRAFT,     // 20% черновиков
                AdsStatusEnum::DRAFT,
                AdsStatusEnum::ARCHIVED,  // 10% архивных
            ]),
            'is_urgent' => $this->faker->boolean(0.2), // 20% срочных
            'features' => $this->faker->optional(0.6)->randomElements(
                $this->getFeaturesForCategory($category),
                $this->faker->numberBetween(1, 4)
            ),
        ];
    }
    
    private function getSubcategoriesForCategory(AdCategoryEnum $category): array
    {
        // Пока просто вернем все subcategories, в реальном проекте это должно быть связано
        return AdSubcategoryEnum::cases();
    }
    
    private function generateTitleByType(AdTypeEnum $type, AdCategoryEnum $category): string
    {
        $titles = match ([$type, $category]) {
            [AdTypeEnum::GOODS, AdCategoryEnum::MATERIALS] => [
                'Кирпич керамический М-150',
                'Цемент Портланд 500',
                'Песок строительный',
                'Щебень гранитный 20-40мм',
                'Арматура 12мм А500С',
            ],
            [AdTypeEnum::GOODS, AdCategoryEnum::TOOLS] => [
                'Перфоратор Bosch 2-26',
                'Шуруповерт Makita 18V',
                'Лом монтажный 1.5м',
                'Лопата совковая',
                'Молоток 500г',
            ],
            [AdTypeEnum::GOODS, AdCategoryEnum::EQUIPMENT] => [
                'Бетономешалка 180л',
                'Компрессор 50л',
                'Леса строительные',
                'Мини-экскаватор',
            ],
            [AdTypeEnum::SERVICES, AdCategoryEnum::CONSTRUCTION] => [
                'Строительство домов под ключ',
                'Заливка фундамента',
                'Кладка кирпича',
                'Монтаж кровли',
            ],
            [AdTypeEnum::SERVICES, AdCategoryEnum::REPAIR] => [
                'Косметический ремонт квартир',
                'Ремонт санузла под ключ',
                'Поклейка обоев',
                'Малярные работы',
            ],
            [AdTypeEnum::SERVICES, AdCategoryEnum::DESIGN] => [
                'Проект дома',
                'Дизайн интерьера',
                'Проект ландшафта',
                'Архитектурное проектирование',
            ],
            default => ['Товар/услуга']
        };
        
        return $this->faker->randomElement($titles);
    }
    
    private function generateDescriptionByType(AdTypeEnum $type, AdCategoryEnum $category): string
    {
        $templates = match ([$type, $category]) {
            [AdTypeEnum::GOODS, AdCategoryEnum::MATERIALS] => [
                'Качественные строительные материалы по выгодным ценам. Оптом и в розницу. Доставка включена.',
                'Продаем стройматериалы высокого качества. Обеспечиваем быструю доставку по городу.',
            ],
            [AdTypeEnum::GOODS, AdCategoryEnum::TOOLS] => [
                'Профессиональные строительные инструменты. Продажа и аренда.',
                'Качественный инструмент от известных производителей. Гарантия от магазина.',
            ],
            [AdTypeEnum::SERVICES, AdCategoryEnum::CONSTRUCTION] => [
                'Оказываем качественные строительные услуги. Опыт работы более 10 лет. Команда профессионалов.',
                'Полный цикл строительных работ от фундамента до кровли. Гарантия на все работы.',
            ],
            [AdTypeEnum::SERVICES, AdCategoryEnum::REPAIR] => [
                'Ремонт квартир и офисов любой сложности. Качественно и в срок.',
                'Отделочные работы любой сложности. Собственные специалисты.',
            ],
            default => ['Качественная услуга/товар по выгодным ценам.']
        };
        
        return $this->faker->randomElement($templates);
    }
    
    private function getFeaturesForCategory(AdCategoryEnum $category): array
    {
        return match ($category) {
            AdCategoryEnum::MATERIALS => [
                'Морозостойкость', 'Прочность', 'Легкость', 'Низкая цена', 'Экологичность'
            ],
            AdCategoryEnum::TOOLS => [
                'Мощность', 'Надежность', 'Легкость', 'Компактность', 'Гарантия'
            ],
            AdCategoryEnum::EQUIPMENT => [
                'Производительность', 'Надежность', 'Экономичность', 'Мобильность'
            ],
            AdCategoryEnum::CONSTRUCTION => [
                'Опыт', 'Качество', 'Срочность', 'Гарантия', 'Лицензия'
            ],
            AdCategoryEnum::REPAIR => [
                'Качество', 'Скорость', 'Недорого', 'Опыт', 'Материалы в комплекте'
            ],
            AdCategoryEnum::DESIGN => [
                'Креативность', 'Профессионализм', 'Инновации', 'Индивидуальный подход'
            ],
        };
    }
}
