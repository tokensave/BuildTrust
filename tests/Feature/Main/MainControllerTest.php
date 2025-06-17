<?php

declare(strict_types=1);
declare(ticks=1000);

namespace Tests\Feature\Main;

use App\Enums\AdEnums\AdsStatusEnum;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MainControllerTest extends TestCase
{
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
}
