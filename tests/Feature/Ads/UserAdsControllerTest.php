<?php

declare(strict_types=1);
declare(ticks=1000);

namespace Tests\Feature\Ads;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_ads_index(): void
    {
        $user = User::factory()->create();
        Ad::factory()->count(2)->for($user)->create();

        $this->actingAs($user)
            ->get(route('user.ads.index', $user->id))
            ->assertOk()
            ->assertInertia(fn ($page) =>
            $page->component('ads/userAds/Index')
                ->has('ads', 2)
            );
    }

    public function test_user_can_view_single_ad(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('user.ads.show', [$user->id, $ad->id]))
            ->assertOk()
            ->assertInertia(fn ($page) =>
            $page->component('ads/userAds/Show')
                ->where('ad.id', $ad->id)
            );
    }

    public function test_user_can_see_create_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.ads.create', $user->id))
            ->assertOk()
            ->assertInertia(fn ($page) =>
            $page->component('ads/userAds/Create')
            );
    }

    public function test_user_can_store_new_ad(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Тестовое объявление',
            'type' => AdTypeEnum::GOODS->value,
            'category' => AdCategoryEnum::MATERIALS->value,
            'subcategory' => AdSubcategoryEnum::CONCRETE->value,
            'location' => 'Москва',
            'description' => 'Описание',
            'price' => 1000,
            'is_urgent' => false,
            'features' => ['Качественный', 'Надёжный'],
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Тестовое объявление',
            'type' => AdTypeEnum::GOODS->value,
            'category' => AdCategoryEnum::MATERIALS->value,
            'subcategory' => AdSubcategoryEnum::CONCRETE->value,
            'location' => 'Москва',
            'description' => 'Описание',
            'price' => 1000,
            'is_urgent' => false,
        ]);
    }

    public function test_user_can_store_service_ad(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Услуги строительства',
            'type' => AdTypeEnum::SERVICES->value,
            'category' => AdCategoryEnum::CONSTRUCTION->value,
            'subcategory' => AdSubcategoryEnum::FOUNDATION->value,
            'location' => 'СПб',
            'description' => 'Профессиональные услуги',
            'is_urgent' => true,
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Услуги строительства',
            'type' => AdTypeEnum::SERVICES->value,
            'category' => AdCategoryEnum::CONSTRUCTION->value,
            'is_urgent' => true,
        ]);
    }

    public function test_user_can_see_edit_form(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('user.ads.edit', [$user->id, $ad->id]))
            ->assertOk()
            ->assertInertia(fn ($page) =>
            $page->component('ads/userAds/Edit')
                ->where('ad.id', $ad->id)
            );
    }

    public function test_user_can_update_ad(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->for($user)->create([
            'title' => 'Старое название',
            'description' => 'Старое описание',
            'price' => 500,
        ]);

        $this->actingAs($user);

        $updateData = [
            'title' => 'Новое название',
            'type' => AdTypeEnum::SERVICES->value,
            'description' => 'Новое описание',
            'price' => 2000,
            'status' => AdsStatusEnum::DRAFT->value
        ];

        $this->post(route('user.ads.update', [$user->id, $ad->id]), $updateData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'title' => 'Новое название',
            'description' => 'Новое описание',
            'price' => 2000,
        ]);
    }

    public function test_user_can_delete_ad(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('user.ads.destroy', [$user->id, $ad->id]))
            ->assertRedirect();

        $this->assertSoftDeleted('ads', [
            'id' => $ad->id,
        ]);
    }

    public function test_user_can_create_ad_with_features(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Объявление с характеристиками',
            'type' => AdTypeEnum::GOODS->value,
            'category' => AdCategoryEnum::MATERIALS->value,
            'subcategory' => AdSubcategoryEnum::CONCRETE->value,
            'location' => 'Москва',
            'description' => 'Описание товара',
            'price' => 1500,
            'is_urgent' => true,
            'features' => ['Прочный', 'Морозостойкий', 'Быстротвердеющий'],
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Объявление с характеристиками',
            'is_urgent' => true,
        ]);

        $ad = Ad::where('title', 'Объявление с характеристиками')->first();
        $this->assertNotNull($ad);
        $this->assertEquals(['Прочный', 'Морозостойкий', 'Быстротвердеющий'], $ad->features);
    }

    public function test_user_can_create_urgent_ad(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Срочное объявление',
            'type' => AdTypeEnum::SERVICES->value,
            'category' => AdCategoryEnum::CONSTRUCTION->value,
            'location' => 'СПб',
            'description' => 'Срочно нужны услуги',
            'is_urgent' => true,
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Срочное объявление',
            'is_urgent' => true,
        ]);
    }

    public function test_user_can_update_ad_with_all_fields(): void
    {
        $user = User::factory()->create();
        $ad = Ad::factory()->for($user)->create([
            'title' => 'Старое название',
            'type' => AdTypeEnum::GOODS->value,
            'category' => AdCategoryEnum::MATERIALS->value,
            'subcategory' => AdSubcategoryEnum::CONCRETE->value,
            'description' => 'Старое описание',
            'price' => 500,
            'is_urgent' => false,
            'features' => ['Старая характеристика'],
            'status' => AdsStatusEnum::DRAFT->value,
        ]);

        $this->actingAs($user);

        $updateData = [
            'title' => 'Обновленное название',
            'type' => AdTypeEnum::SERVICES->value,
            'category' => AdCategoryEnum::CONSTRUCTION->value,
            'subcategory' => AdSubcategoryEnum::FOUNDATION->value,
            'location' => 'Новосибирск',
            'description' => 'Обновленное описание',
            'price' => 2500,
            'is_urgent' => true,
            'features' => ['Новая характеристика', 'Улучшенное качество'],
            'status' => AdsStatusEnum::PUBLISHED->value,
        ];

        $this->post(route('user.ads.update', [$user->id, $ad->id]), $updateData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'title' => 'Обновленное название',
            'type' => AdTypeEnum::SERVICES->value,
            'category' => AdCategoryEnum::CONSTRUCTION->value,
            'subcategory' => AdSubcategoryEnum::FOUNDATION->value,
            'location' => 'Новосибирск',
            'description' => 'Обновленное описание',
            'price' => 2500,
            'is_urgent' => true,
            'status' => AdsStatusEnum::PUBLISHED->value,
        ]);

        $ad->refresh();
        $this->assertEquals(['Новая характеристика', 'Улучшенное качество'], $ad->features);
    }

    public function test_validation_fails_when_subcategory_does_not_match_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Тестовое объявление',
            'type' => AdTypeEnum::GOODS->value,
            'category' => AdCategoryEnum::MATERIALS->value,
            'subcategory' => AdSubcategoryEnum::FOUNDATION->value, // Не соответствует категории MATERIALS
            'location' => 'Москва',
            'description' => 'Описание',
            'price' => 1000,
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertSessionHasErrors('subcategory');

        $this->assertDatabaseMissing('ads', [
            'user_id' => $user->id,
            'title' => 'Тестовое объявление',
        ]);
    }

    public function test_can_create_ad_without_optional_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Минимальное объявление',
            'type' => AdTypeEnum::SERVICES->value,
            'description' => 'Базовое описание',
            'is_urgent' => false,
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Минимальное объявление',
            'category' => null,
            'subcategory' => null,
            'location' => null,
            'price' => null,
        ]);
    }

    public function test_features_are_properly_validated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Тест с пустым массивом features
        $postData = [
            'title' => 'Объявление с пустыми характеристиками',
            'type' => AdTypeEnum::GOODS->value,
            'description' => 'Описание',
            'features' => [],
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Объявление с пустыми характеристиками',
        ]);
    }

    public function test_price_validation_works_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Тест с отрицательной ценой
        $postData = [
            'title' => 'Тестовое объявление',
            'type' => AdTypeEnum::GOODS->value,
            'description' => 'Описание',
            'price' => -100,
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertSessionHasErrors('price');

        // Тест с нулевой ценой (должно проходить)
        $postData['price'] = 0;
        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_status_defaults_to_draft_when_not_provided(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Объявление без статуса',
            'type' => AdTypeEnum::GOODS->value,
            'description' => 'Описание',
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Объявление без статуса',
            'status' => AdsStatusEnum::DRAFT->value,
        ]);
    }

    public function test_slug_is_generated_automatically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => 'Тестовое Объявление С Пробелами',
            'type' => AdTypeEnum::GOODS->value,
            'description' => 'Описание',
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $ad = Ad::where('title', 'Тестовое Объявление С Пробелами')->first();
        $this->assertNotNull($ad);
        $this->assertNotEmpty($ad->slug);
    }
}
