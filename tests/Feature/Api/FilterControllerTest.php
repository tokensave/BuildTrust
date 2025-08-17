<?php

declare(strict_types=1);
declare(ticks=1000);

namespace Tests\Feature\Api;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_all_types(): void
    {
        $response = $this->get('/api/filters/types');
        
        $response->assertOk()
                 ->assertJsonStructure([
                     'goods' => [
                         'value',
                         'label', 
                         'description'
                     ],
                     'services' => [
                         'value',
                         'label',
                         'description'
                     ]
                 ]);
    }

    public function test_it_returns_all_categories(): void
    {
        $response = $this->get('/api/filters/categories');
        
        $response->assertOk()
                 ->assertJsonStructure([
                     'materials' => ['value', 'label'],
                     'tools' => ['value', 'label'],
                     'equipment' => ['value', 'label'],
                     'construction' => ['value', 'label'],
                     'repair' => ['value', 'label'],
                     'design' => ['value', 'label'],
                 ]);
    }

    public function test_it_returns_categories_by_type(): void
    {
        $response = $this->get('/api/filters/categories-by-type?type=' . AdTypeEnum::GOODS->value);
        
        $response->assertOk()
                 ->assertJsonStructure([
                     'materials' => ['value', 'label'],
                     'tools' => ['value', 'label'],
                     'equipment' => ['value', 'label']
                 ]);
    }

    public function test_it_returns_subcategories_by_category(): void
    {
        $response = $this->get('/api/filters/subcategories?category=' . AdCategoryEnum::MATERIALS->value);
        
        $response->assertOk()
                 ->assertJsonStructure([
                     'concrete' => ['value', 'label'],
                     'brick' => ['value', 'label'],
                 ]);
    }

    public function test_it_returns_error_for_missing_type_parameter(): void
    {
        $response = $this->get('/api/filters/categories-by-type');
        
        $response->assertStatus(400)
                 ->assertJson(['error' => 'Type parameter is required']);
    }

    public function test_it_returns_error_for_invalid_type(): void
    {
        $response = $this->get('/api/filters/categories-by-type?type=invalid');
        
        $response->assertStatus(400)
                 ->assertJson(['error' => 'Invalid type']);
    }

    public function test_it_returns_error_for_missing_category_parameter(): void
    {
        $response = $this->get('/api/filters/subcategories');
        
        $response->assertStatus(400)
                 ->assertJson(['error' => 'Category parameter is required']);
    }

    public function test_it_returns_error_for_invalid_category(): void
    {
        $response = $this->get('/api/filters/subcategories?category=invalid');
        
        $response->assertStatus(400)
                 ->assertJson(['error' => 'Invalid category']);
    }

    public function test_it_returns_full_structure(): void
    {
        $response = $this->get('/api/filters/structure');
        
        $response->assertOk()
                 ->assertJsonStructure([
                     'types' => [
                         'goods' => ['value', 'label', 'description'],
                         'services' => ['value', 'label', 'description']
                     ],
                     'categories_structure' => [
                         'materials' => [
                             'label',
                             'subcategories' => [
                                 'concrete' => ['value', 'label'],
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_it_returns_popular_locations(): void
    {
        $response = $this->get('/api/filters/locations');
        
        $response->assertOk()
                 ->assertJsonStructure([
                     'moscow',
                     'spb',
                     'kazan',
                     'ekaterinburg',
                     'novosibirsk'
                 ]);
    }
}
