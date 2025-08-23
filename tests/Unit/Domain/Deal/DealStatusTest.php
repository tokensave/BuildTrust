<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Deal;

use App\Domain\Deal\ValueObjects\DealStatus;
use PHPUnit\Framework\TestCase;

/**
 * Unit тесты для DealStatus Value Object
 * Тестируем бизнес-логику переходов между статусами
 */
class DealStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_labels(): void
    {
        $this->assertEquals('В ожидании', DealStatus::PENDING->label());
        $this->assertEquals('Принята', DealStatus::ACCEPTED->label());
        $this->assertEquals('Отклонена', DealStatus::REJECTED->label());
        $this->assertEquals('Завершена', DealStatus::COMPLETED->label());
        $this->assertEquals('Отменена', DealStatus::CANCELED->label());
    }

    /** @test */
    public function it_has_correct_css_colors(): void
    {
        $this->assertEquals('bg-yellow-100 text-yellow-800', DealStatus::PENDING->color());
        $this->assertEquals('bg-green-100 text-green-800', DealStatus::COMPLETED->color());
    }

    /** @test */
    public function it_validates_pending_transitions_correctly(): void
    {
        $pending = DealStatus::PENDING;
        
        $this->assertTrue($pending->canTransitionTo(DealStatus::ACCEPTED));
        $this->assertTrue($pending->canTransitionTo(DealStatus::REJECTED));
        $this->assertTrue($pending->canTransitionTo(DealStatus::CANCELED));
        $this->assertFalse($pending->canTransitionTo(DealStatus::COMPLETED));
    }

    /** @test */
    public function it_validates_accepted_transitions_correctly(): void
    {
        $accepted = DealStatus::ACCEPTED;
        
        $this->assertTrue($accepted->canTransitionTo(DealStatus::COMPLETED));
        $this->assertTrue($accepted->canTransitionTo(DealStatus::CANCELED));
        $this->assertFalse($accepted->canTransitionTo(DealStatus::PENDING));
        $this->assertFalse($accepted->canTransitionTo(DealStatus::REJECTED));
    }

    /** @test */
    public function it_prevents_transitions_from_final_statuses(): void
    {
        $this->assertFalse(DealStatus::REJECTED->canTransitionTo(DealStatus::PENDING));
        $this->assertFalse(DealStatus::COMPLETED->canTransitionTo(DealStatus::PENDING));
        $this->assertFalse(DealStatus::CANCELED->canTransitionTo(DealStatus::ACCEPTED));
    }

    /** @test */
    public function it_identifies_final_statuses_correctly(): void
    {
        $this->assertFalse(DealStatus::PENDING->isFinal());
        $this->assertFalse(DealStatus::ACCEPTED->isFinal());
        $this->assertTrue(DealStatus::REJECTED->isFinal());
        $this->assertTrue(DealStatus::COMPLETED->isFinal());
        $this->assertTrue(DealStatus::CANCELED->isFinal());
    }

    /** @test */
    public function it_identifies_active_statuses_correctly(): void
    {
        $this->assertTrue(DealStatus::PENDING->isActive());
        $this->assertTrue(DealStatus::ACCEPTED->isActive());
        $this->assertFalse(DealStatus::REJECTED->isActive());
        $this->assertFalse(DealStatus::COMPLETED->isActive());
        $this->assertFalse(DealStatus::CANCELED->isActive());
    }

    /** @test */
    public function it_provides_available_transitions(): void
    {
        $pendingTransitions = DealStatus::PENDING->getAvailableTransitions();
        $this->assertCount(3, $pendingTransitions);
        $this->assertContains(DealStatus::ACCEPTED, $pendingTransitions);
        $this->assertContains(DealStatus::REJECTED, $pendingTransitions);
        $this->assertContains(DealStatus::CANCELED, $pendingTransitions);

        $acceptedTransitions = DealStatus::ACCEPTED->getAvailableTransitions();
        $this->assertCount(2, $acceptedTransitions);
        $this->assertContains(DealStatus::COMPLETED, $acceptedTransitions);
        $this->assertContains(DealStatus::CANCELED, $acceptedTransitions);

        $rejectedTransitions = DealStatus::REJECTED->getAvailableTransitions();
        $this->assertCount(0, $rejectedTransitions);
    }
}
