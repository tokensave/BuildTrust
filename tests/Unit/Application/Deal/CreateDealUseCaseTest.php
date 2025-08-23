<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Deal;

use App\Application\Deal\DTOs\CreateDealCommand;
use App\Application\Deal\UseCases\CreateDealUseCase;
use App\Domain\Deal\Contracts\DealRepositoryInterface;
use App\Domain\Deal\Entities\Deal;
use App\Shared\ValueObjects\Ids\DealId;
use Tests\TestCase;
use Mockery;

/**
 * Unit тесты для CreateDealUseCase
 * Тестируем бизнес-логику создания сделки изолированно от инфраструктуры
 */
class CreateDealUseCaseTest extends TestCase
{
    private DealRepositoryInterface $mockRepository;
    private CreateDealUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(DealRepositoryInterface::class);
        $this->useCase = new CreateDealUseCase($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_deal_successfully_with_valid_command(): void
    {
        // Arrange
        $command = CreateDealCommand::fromArray([
            'ad_id' => 100,
            'buyer_id' => 10,
            'seller_id' => 20,
            'price' => 1000.50,
            'notes' => 'Test deal creation'
        ]);

        $this->mockRepository
            ->expects('nextId')
            ->once()
            ->andReturn(DealId::fromInt(1));

        $this->mockRepository
            ->expects('save')
            ->once()
            ->with(Mockery::type(Deal::class));

        // Act
        $deal = $this->useCase->execute($command);

        // Assert
        $this->assertEquals(1, $deal->getId()->toInt());
        $this->assertEquals(100, $deal->getAdId()->toInt());
        $this->assertEquals(10, $deal->getBuyerId()->toInt());
        $this->assertEquals(20, $deal->getSellerId()->toInt());
        $this->assertEquals(1000.50, $deal->getPrice()->toRubles());
        $this->assertEquals('Test deal creation', $deal->getNotes()->toString());
    }

    /** @test */
    public function it_throws_exception_when_buyer_equals_seller(): void
    {
        // Arrange
        $command = CreateDealCommand::fromArray([
            'ad_id' => 100,
            'buyer_id' => 10,
            'seller_id' => 10, // Same as buyer!
            'price' => 1000.50,
        ]);

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Покупатель и продавец не могут быть одним лицом');

        $this->useCase->execute($command);
    }

    /** @test */
    public function it_throws_exception_when_price_is_negative(): void
    {
        // Arrange
        $command = CreateDealCommand::fromArray([
            'ad_id' => 100,
            'buyer_id' => 10,
            'seller_id' => 20,
            'price' => -100.0, // Negative price!
        ]);

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Цена должна быть положительной');

        $this->useCase->execute($command);
    }

    /** @test */
    public function it_throws_exception_when_price_is_zero(): void
    {
        // Arrange
        $command = CreateDealCommand::fromArray([
            'ad_id' => 100,
            'buyer_id' => 10,
            'seller_id' => 20,
            'price' => 0.0, // Zero price!
        ]);

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Цена должна быть положительной');

        $this->useCase->execute($command);
    }

    /** @test */
    public function it_creates_deal_without_notes(): void
    {
        // Arrange
        $command = CreateDealCommand::fromArray([
            'ad_id' => 100,
            'buyer_id' => 10,
            'seller_id' => 20,
            'price' => 1000.50,
            'notes' => null // No notes
        ]);

        $this->mockRepository
            ->expects('nextId')
            ->once()
            ->andReturn(DealId::fromInt(1));

        $this->mockRepository
            ->expects('save')
            ->once();

        // Act
        $deal = $this->useCase->execute($command);

        // Assert
        $this->assertNull($deal->getNotes());
    }
}
