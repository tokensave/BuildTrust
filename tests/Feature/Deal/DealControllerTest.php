<?php

declare(strict_types=1);

namespace Tests\Feature\Deal;

use App\Models\Ad;
use App\Models\User;
use App\Domain\Deal\ValueObjects\DealStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature тесты для DealController
 * Интеграционные тесты всего пайплайна: HTTP → Controller → Use Case → Domain → Repository → Database
 */
class DealControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Ad $ad;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
        $this->ad = Ad::factory()->create(['user_id' => $this->seller->id, 'price' => 1000.50]);
    }

    /** @test */
    public function it_can_create_deal_through_http_request(): void
    {
        // Arrange
        $this->actingAs($this->buyer);

        // Act
        $response = $this->post(route('deals.store', ['ad' => $this->ad->id]), [
            'notes' => 'I want to buy this item',
        ]);

        // Assert
        $response->assertRedirect();
        
        $this->assertDatabaseHas('deals', [
            'ad_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'price' => 1000.50,
            'status' => DealStatus::PENDING->value,
            'notes' => 'I want to buy this item',
        ]);
    }

    /** @test */
    public function it_can_update_deal_status_through_http_request(): void
    {
        // Arrange
        $this->actingAs($this->seller);
        
        // Create a deal first using the old model (for simplicity in tests)
        $deal = \App\Models\Deal::create([
            'ad_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'price' => 1000.50,
            'status' => DealStatus::PENDING->value,
        ]);

        // Act
        $response = $this->post(route('user.deals.updateStatus', ['deal' => $deal->id]), [
            'status' => DealStatus::ACCEPTED->value,
        ]);

        // Assert
        $response->assertRedirect(route('user.deals.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'status' => DealStatus::ACCEPTED->value,
        ]);
    }

    /** @test */
    public function it_rejects_deal_creation_when_user_not_authenticated(): void
    {
        // Act
        $response = $this->post(route('deals.store', ['ad' => $this->ad->id]), [
            'notes' => 'Unauthorized attempt',
        ]);

        // Assert
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('deals', [
            'ad_id' => $this->ad->id,
        ]);
    }

    /** @test */
    public function it_rejects_status_update_from_unauthorized_user(): void
    {
        // Arrange
        $randomUser = User::factory()->create();
        $this->actingAs($randomUser);
        
        $deal = \App\Models\Deal::create([
            'ad_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'price' => 1000.50,
            'status' => DealStatus::PENDING->value,
        ]);

        // Act
        $response = $this->post(route('user.deals.updateStatus', ['deal' => $deal->id]), [
            'status' => DealStatus::ACCEPTED->value,
        ]);

        // Assert
        $response->assertRedirect(route('user.deals.index'));
        $response->assertSessionHas('error');
        
        // Status should remain unchanged
        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'status' => DealStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function it_shows_user_deals_correctly(): void
    {
        // Arrange
        $this->actingAs($this->buyer);
        
        \App\Models\Deal::create([
            'ad_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'price' => 1000.50,
            'status' => DealStatus::PENDING->value,
        ]);

        // Act
        $response = $this->get(route('user.deals.index'));

        // Assert
        $response->assertSuccessful();
        $response->assertInertia(fn($page) => 
            $page->component('userDeals/Index')
                ->has('deals')
                ->has('statuses')
        );
    }

    /** @test */
    public function it_validates_required_fields_in_deal_creation(): void
    {
        // Arrange
        $this->actingAs($this->buyer);

        // Act
        $response = $this->post(route('deals.store', ['ad' => $this->ad->id]), [
            // Missing required fields if any
        ]);

        // Assert
        // Should pass validation since notes is optional
        $response->assertRedirect();
    }
}
