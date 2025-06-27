<?php

declare(strict_types=1);
declare(ticks=1000);

namespace Tests\Feature\Ads;

use App\Enums\AdEnums\AdsStatusEnum;
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
            'description' => 'Описание',
            'price' => 1000,
            // Добавь сюда другие обязательные поля, если нужно
        ];

        $this->post(route('user.ads.store', $user->id), $postData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'Тестовое объявление',
            'description' => 'Описание',
            'price' => 1000,
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
            'description' => 'Новое описание',
            'price' => 2000,
            'status' => AdsStatusEnum::DRAFT->value
            // Добавь остальные обязательные поля, если есть
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
}
