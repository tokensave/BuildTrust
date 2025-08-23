<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Deal;

use App\Domain\Deal\Entities\Deal;
use App\Domain\Deal\ValueObjects\DealNotes;
use App\Domain\Deal\ValueObjects\DealStatus;
use App\Shared\ValueObjects\Ids\AdId;
use App\Shared\ValueObjects\Ids\DealId;
use App\Shared\ValueObjects\Ids\UserId;
use App\Shared\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

/**
 * Unit тесты для Deal Entity
 * Тестируем всю бизнес-логику доменной сущности
 */
class DealTest extends TestCase
{
    /** @test */
    public function it_can_create_new_deal_with_pending_status(): void
    {
        $deal = Deal::create(
            id: DealId::fromInt(1),
            adId: AdId::fromInt(100),
            buyerId: UserId::fromInt(10),
            sellerId: UserId::fromInt(20),
            price: Money::fromRubles(1000.50),
            notes: DealNotes::fromString('Test notes')
        );

        $this->assertEquals(1, $deal->getId()->toInt());
        $this->assertEquals(DealStatus::PENDING, $deal->getStatus());
        $this->assertEquals(1000.50, $deal->getPrice()->toRubles());
        $this->assertEquals('Test notes', $deal->getNotes()->toString());
        $this->assertInstanceOf(\DateTimeImmutable::class, $deal->getCreatedAt());
    }

    /** @test */
    public function it_can_accept_deal(): void
    {
        $deal = $this->createTestDeal();
        
        $deal->accept();
        
        $this->assertEquals(DealStatus::ACCEPTED, $deal->getStatus());
    }

    /** @test */
    public function it_can_reject_deal_with_reason(): void
    {
        $deal = $this->createTestDeal();
        
        $deal->reject('Price too high');
        
        $this->assertEquals(DealStatus::REJECTED, $deal->getStatus());
        $this->assertStringContainsString('Причина отклонения: Price too high', $deal->getNotes()->toString());
    }

    /** @test */
    public function it_can_complete_accepted_deal(): void
    {
        $deal = $this->createTestDeal();
        $deal->accept();
        
        $deal->complete();
        
        $this->assertEquals(DealStatus::COMPLETED, $deal->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $deal->getSignedAt());
    }

    /** @test */
    public function it_can_cancel_deal_with_reason(): void
    {
        $deal = $this->createTestDeal();
        
        $deal->cancel('Changed my mind');
        
        $this->assertEquals(DealStatus::CANCELED, $deal->getStatus());
        $this->assertStringContainsString('Причина отмены: Changed my mind', $deal->getNotes()->toString());
    }

    /** @test */
    public function it_validates_user_permissions_correctly(): void
    {
        $deal = $this->createTestDeal();
        $buyerId = UserId::fromInt(10);
        $sellerId = UserId::fromInt(20);
        $randomUserId = UserId::fromInt(999);
        
        $this->assertTrue($deal->canBeModifiedBy($buyerId));
        $this->assertTrue($deal->canBeModifiedBy($sellerId));
        $this->assertFalse($deal->canBeModifiedBy($randomUserId));
        
        $this->assertTrue($deal->isBuyer($buyerId));
        $this->assertFalse($deal->isBuyer($sellerId));
        
        $this->assertTrue($deal->isSeller($sellerId));
        $this->assertFalse($deal->isSeller($buyerId));
    }

    /** @test */
    public function it_can_mark_deal_as_on_chain(): void
    {
        $deal = $this->createTestDeal();
        
        $this->assertFalse($deal->isOnChain());
        
        $deal->markAsOnChain('0x123456789');
        
        $this->assertTrue($deal->isOnChain());
        $this->assertEquals('0x123456789', $deal->getOnChainId());
    }

    /** @test */
    public function it_can_reconstruct_deal_from_database_data(): void
    {
        $deal = Deal::reconstruct(
            id: DealId::fromInt(1),
            adId: AdId::fromInt(100),
            buyerId: UserId::fromInt(10),
            sellerId: UserId::fromInt(20),
            price: Money::fromRubles(1000.50),
            status: DealStatus::ACCEPTED,
            notes: DealNotes::fromString('Reconstructed notes'),
            onChainId: '0x123',
            uuid: 'test-uuid-123',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            signedAt: new \DateTimeImmutable('2024-01-02 12:00:00')
        );

        $this->assertEquals(DealStatus::ACCEPTED, $deal->getStatus());
        $this->assertEquals('0x123', $deal->getOnChainId());
        $this->assertEquals('test-uuid-123', $deal->getUuid());
        $this->assertTrue($deal->isOnChain());
    }

    private function createTestDeal(): Deal
    {
        return Deal::create(
            id: DealId::fromInt(1),
            adId: AdId::fromInt(100),
            buyerId: UserId::fromInt(10),
            sellerId: UserId::fromInt(20),
            price: Money::fromRubles(1000.50)
        );
    }
}
