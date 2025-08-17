<?php

declare(strict_types=1);
declare(ticks=1000);

namespace Tests\Feature\Main;

use App\Enums\AdEnums\AdCategoryEnum;
use App\Enums\AdEnums\AdsStatusEnum;
use App\Enums\AdEnums\AdSubcategoryEnum;
use App\Enums\AdEnums\AdTypeEnum;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MainControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_dashboard_with_published_ads_only(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $publishedAd = Ad::factory()->create([
            'user_id' => $user->id,
            'status' => AdsStatusEnum::PUBLISHED->value,
            'title' => 'Published Ad',
        ]);

        $draftAd = Ad::factory()->create([
            'status' => AdsStatusEnum::DRAFT->value,
            'title' => 'Draft Ad',
        ]);


        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', $publishedAd->title)
                ->where('ads.0.status', AdsStatusEnum::PUBLISHED->value)
            );
    }

    public function test_it_filters_ads_by_type(): void
    {
        $user = User::factory()->create();

        $goodsAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'type' => AdTypeEnum::GOODS->value,
            'title' => 'Goods Ad',
        ]);

        $serviceAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'type' => AdTypeEnum::SERVICES->value,
            'title' => 'Service Ad',
        ]);

        $this->actingAs($user)
            ->get('/dashboard?type=' . AdTypeEnum::GOODS->value)
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', 'Goods Ad')
                ->where('ads.0.type', AdTypeEnum::GOODS->value)
            );
    }

    public function test_it_filters_ads_by_category(): void
    {
        $user = User::factory()->create();

        $materialsAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'category' => AdCategoryEnum::MATERIALS->value,
            'title' => 'Materials Ad',
        ]);

        $toolsAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'category' => AdCategoryEnum::TOOLS->value,
            'title' => 'Tools Ad',
        ]);

        $this->actingAs($user)
            ->get('/dashboard?category=' . AdCategoryEnum::MATERIALS->value)
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', 'Materials Ad')
                ->where('ads.0.category', AdCategoryEnum::MATERIALS->value)
            );
    }

    public function test_it_filters_ads_by_location(): void
    {
        $user = User::factory()->create();

        $moscowAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'location' => 'Москва',
            'title' => 'Moscow Ad',
        ]);

        $spbAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'location' => 'СПб',
            'title' => 'SPB Ad',
        ]);

        $this->actingAs($user)
            ->get('/dashboard?location=Москва')
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', 'Moscow Ad')
                ->where('ads.0.location', 'Москва')
            );
    }

    public function test_it_filters_ads_by_urgent(): void
    {
        $user = User::factory()->create();

        $urgentAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'is_urgent' => true,
            'title' => 'Urgent Ad',
        ]);

        $normalAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'is_urgent' => false,
            'title' => 'Normal Ad',
        ]);

        $this->actingAs($user)
            ->get('/dashboard?urgent=1')
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', 'Urgent Ad')
                ->where('ads.0.is_urgent', true)
            );
    }

    public function test_it_filters_ads_by_price_range(): void
    {
        $user = User::factory()->create();

        $cheapAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'price' => 500,
            'title' => 'Cheap Ad',
        ]);

        $expensiveAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'price' => 5000,
            'title' => 'Expensive Ad',
        ]);

        $this->actingAs($user)
            ->get('/dashboard?min_price=1000&max_price=10000')
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', 'Expensive Ad')
                ->where('ads.0.price', '5000.00')
            );
    }

    public function test_it_searches_ads_by_text(): void
    {
        $user = User::factory()->create();

        $matchingAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'title' => 'Качественные материалы',
            'description' => 'Описание материалов',
        ]);

        $nonMatchingAd = Ad::factory()->create([
            'status' => AdsStatusEnum::PUBLISHED->value,
            'title' => 'Другое объявление',
            'description' => 'Описание чего-то другого',
        ]);

        $this->actingAs($user)
            ->get('/dashboard?search=материал')
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('ads', 1)
                ->where('ads.0.title', 'Качественные материалы')
            );
    }
}
