# 🔄 Рефакторинг Deal домена в DDD - Пошаговый план

## 🎯 Анализ текущего кода

### **Что у вас есть сейчас:**
```
Models/Deal.php                           # Eloquent модель без бизнес-логики
Services/Deal/DealService.php            # Сервис со всей логикой
Events/DealCreatedOrUpdatedEvent.php     # Laravel Event
Listeners/SendDealToBlockchainMiddleware.php # Отправка в Go микросервис  
DTO/Deal/StoreDealData.php               # DTO для создания сделки
Enums/DealEnums/DealStatusEnum.php       # Статусы сделок
```

### **Что видим в коде:**
1. ✅ **Хорошо:** Есть enum для статусов, есть DTO, есть event
2. ❌ **Проблемы:** Нет бизнес-логики, нет валидации переходов статусов
3. 🔗 **Микросервис:** Есть отправка данных в блокчейн сервис на Go

---

## 🏗️ **После DDD будет:**

```
Domain/Deal/                                    # БИЗНЕС-ЛОГИКА
├── Entities/Deal.php                          # Главная сущность с правилами
├── ValueObjects/DealPrice.php                 # Цена с валидацией
├── ValueObjects/DealNotes.php                 # Заметки с лимитами  
├── Services/DealStatusTransitionService.php   # Правила смены статусов
├── Events/DealWasCreated.php                 # Доменное событие
├── Events/DealStatusWasChanged.php           # Событие смены статуса
├── Contracts/DealRepositoryInterface.php     # Интерфейс репозитория
├── Contracts/BlockchainServiceInterface.php  # Интерфейс для блокчейн
└── Exceptions/InvalidDealTransitionException.php

Application/Deal/                               # КООРДИНАЦИЯ
├── Services/DealApplicationService.php       # Оркестратор операций
├── Commands/CreateDealCommand.php            # Команда создания
├── Commands/ChangeDealStatusCommand.php      # Команда смены статуса
├── Queries/GetUserDealsQuery.php            # Запрос сделок пользователя
└── Handlers/CreateDealCommandHandler.php    # Обработчик команды

Infrastructure/Deal/                            # ТЕХНИКА
├── Models/DealModel.php                      # Чистая Eloquent модель
├── Repositories/EloquentDealRepository.php   # Работа с БД
├── Services/BlockchainHttpService.php        # HTTP клиент для Go сервиса
└── EventHandlers/SendDealToBlockchainHandler.php # Обработчик события

Events/DealCreatedOrUpdatedEvent.php           # Остается для совместимости
```

---

## 📅 **Пошаговый план (1 неделя)**

### **🔥 День 1: Value Objects и ID типы**

#### 1.1 Создаем базовые Value Objects
```php
// app/Shared/ValueObjects/Money.php
final class Money {
    private readonly int $amountInKopecks;
    
    public function __construct(int $amountInKopecks) {
        if ($amountInKopecks < 0) {
            throw new InvalidMoneyException('Сумма не может быть отрицательной');
        }
        $this->amountInKopecks = $amountInKopecks;
    }
    
    public static function fromRubles(float $rubles): self {
        return new self((int) round($rubles * 100));
    }
    
    public function toRubles(): float {
        return $this->amountInKopecks / 100;
    }
    
    public function format(): string {
        return number_format($this->toRubles(), 2, '.', ' ') . ' ₽';
    }
}
```

#### 1.2 Создаем типобезопасные ID
```php
// app/Shared/ValueObjects/DealId.php
final class DealId {
    private readonly int $value;
    
    public function __construct(int $value) {
        if ($value <= 0) {
            throw new InvalidArgumentException('Deal ID должен быть положительным');
        }
        $this->value = $value;
    }
    
    public static function fromInt(int $id): self {
        return new self($id);
    }
    
    public function toInt(): int {
        return $this->value;
    }
    
    public function equals(DealId $other): bool {
        return $this->value === $other->value;
    }
}

// app/Shared/ValueObjects/AdId.php (аналогично)
// app/Shared/ValueObjects/UserId.php (аналогично)
```

#### 1.3 Создаем доменные Value Objects
```php
// app/Domain/Deal/ValueObjects/DealNotes.php
final class DealNotes {
    private readonly string $value;
    
    public function __construct(string $notes) {
        $trimmed = trim($notes);
        
        if (strlen($trimmed) > 1000) {
            throw new InvalidArgumentException('Заметки не могут быть длиннее 1000 символов');
        }
        
        $this->value = $trimmed;
    }
    
    public static function fromString(?string $notes): ?self {
        return $notes ? new self($notes) : null;
    }
    
    public function toString(): string {
        return $this->value;
    }
    
    public function isEmpty(): bool {
        return empty($this->value);
    }
}
```

---

### **🏛️ День 2: Domain Entity**

#### 2.1 Создаем главную доменную сущность
```php
// app/Domain/Deal/Entities/Deal.php
final class Deal {
    private array $recordedEvents = [];
    
    private function __construct(
        private DealId $id,
        private AdId $adId,
        private UserId $buyerId,
        private UserId $sellerId,
        private Money $price,
        private DealStatus $status,
        private ?DealNotes $notes,
        private ?string $onChainId = null,
        private ?\DateTimeImmutable $signedAt = null
    ) {}
    
    // Фабрика для создания новой сделки
    public static function create(
        DealId $id,
        AdId $adId,
        UserId $buyerId,
        UserId $sellerId,
        Money $price,
        ?DealNotes $notes = null
    ): self {
        $deal = new self(
            id: $id,
            adId: $adId,
            buyerId: $buyerId,
            sellerId: $sellerId,
            price: $price,
            status: DealStatus::PENDING,
            notes: $notes
        );
        
        $deal->recordThat(new DealWasCreated(
            dealId: $id,
            adId: $adId,
            price: $price,
            occurredAt: new \DateTimeImmutable()
        ));
        
        return $deal;
    }
    
    // Фабрика для восстановления из БД
    public static function reconstruct(
        DealId $id,
        AdId $adId,
        UserId $buyerId,
        UserId $sellerId,
        Money $price,
        DealStatus $status,
        ?DealNotes $notes,
        ?string $onChainId,
        ?\DateTimeImmutable $signedAt
    ): self {
        return new self($id, $adId, $buyerId, $sellerId, $price, $status, $notes, $onChainId, $signedAt);
    }
    
    // Бизнес-логика
    public function canBeConfirmed(): bool {
        return $this->status === DealStatus::PENDING;
    }
    
    public function confirm(): void {
        if (!$this->canBeConfirmed()) {
            throw new InvalidDealTransitionException(
                "Нельзя подтвердить сделку в статусе: {$this->status->value}"
            );
        }
        
        $this->status = DealStatus::CONFIRMED;
        $this->signedAt = new \DateTimeImmutable();
        
        $this->recordThat(new DealStatusWasChanged(
            dealId: $this->id,
            newStatus: $this->status,
            occurredAt: new \DateTimeImmutable()
        ));
    }
    
    public function canBeCancelled(): bool {
        return !in_array($this->status, [DealStatus::COMPLETED, DealStatus::CANCELLED]);
    }
    
    public function cancel(string $reason): void {
        if (!$this->canBeCancelled()) {
            throw new InvalidDealTransitionException(
                "Нельзя отменить сделку в статусе: {$this->status->value}"
            );
        }
        
        $this->status = DealStatus::CANCELLED;
        $this->notes = DealNotes::fromString(
            ($this->notes?->toString() ?? '') . "\n\nПричина отмены: " . $reason
        );
        
        $this->recordThat(new DealStatusWasChanged(
            dealId: $this->id,
            newStatus: $this->status,
            occurredAt: new \DateTimeImmutable()
        ));
    }
    
    public function markAsOnChain(string $onChainId): void {
        $this->onChainId = $onChainId;
    }
    
    // Геттеры
    public function getId(): DealId { return $this->id; }
    public function getAdId(): AdId { return $this->adId; }
    public function getBuyerId(): UserId { return $this->buyerId; }
    public function getSellerId(): UserId { return $this->sellerId; }
    public function getPrice(): Money { return $this->price; }
    public function getStatus(): DealStatus { return $this->status; }
    public function getNotes(): ?DealNotes { return $this->notes; }
    public function getOnChainId(): ?string { return $this->onChainId; }
    public function getSignedAt(): ?\DateTimeImmutable { return $this->signedAt; }
    
    // События
    public function getRecordedEvents(): array {
        return $this->recordedEvents;
    }
    
    public function clearRecordedEvents(): void {
        $this->recordedEvents = [];
    }
    
    private function recordThat(object $event): void {
        $this->recordedEvents[] = $event;
    }
}
```

#### 2.2 Создаем доменные события
```php
// app/Domain/Deal/Events/DealWasCreated.php
final readonly class DealWasCreated {
    public function __construct(
        public DealId $dealId,
        public AdId $adId,
        public Money $price,
        public \DateTimeImmutable $occurredAt
    ) {}
}

// app/Domain/Deal/Events/DealStatusWasChanged.php
final readonly class DealStatusWasChanged {
    public function __construct(
        public DealId $dealId,
        public DealStatus $newStatus,
        public \DateTimeImmutable $occurredAt
    ) {}
}
```

---

### **🎼 День 3: Application Layer**

#### 3.1 Создаем Commands
```php
// app/Application/Deal/Commands/CreateDealCommand.php
final readonly class CreateDealCommand {
    public function __construct(
        public AdId $adId,
        public UserId $buyerId,
        public Money $price,
        public ?DealNotes $notes = null,
        public array $documents = []
    ) {}
}

// app/Application/Deal/Commands/ChangeDealStatusCommand.php
final readonly class ChangeDealStatusCommand {
    public function __construct(
        public DealId $dealId,
        public DealStatus $newStatus,
        public ?string $reason = null
    ) {}
}
```

#### 3.2 Создаем Application Service
```php
// app/Application/Deal/Services/DealApplicationService.php
class DealApplicationService {
    public function __construct(
        private DealRepositoryInterface $dealRepository,
        private AdRepositoryInterface $adRepository,
        private UserRepositoryInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}
    
    public function createDeal(CreateDealCommand $command): DealId {
        return DB::transaction(function () use ($command) {
            // 1. Валидация через репозитории
            $ad = $this->adRepository->findById($command->adId);
            if (!$ad) {
                throw new AdNotFoundException($command->adId);
            }
            
            $buyer = $this->userRepository->findById($command->buyerId);
            if (!$buyer) {
                throw new UserNotFoundException($command->buyerId);
            }
            
            // 2. Создание доменной сущности
            $dealId = $this->dealRepository->nextId();
            $deal = Deal::create(
                id: $dealId,
                adId: $command->adId,
                buyerId: $command->buyerId,
                sellerId: $ad->getUserId(), // Из объявления
                price: $command->price,
                notes: $command->notes
            );
            
            // 3. Сохранение
            $this->dealRepository->save($deal);
            
            // 4. Обработка документов (если есть)
            if (!empty($command->documents)) {
                $this->attachDocuments($deal, $command->documents);
            }
            
            // 5. Отправка событий
            foreach ($deal->getRecordedEvents() as $event) {
                $this->eventDispatcher->dispatch($event);
            }
            $deal->clearRecordedEvents();
            
            return $dealId;
        });
    }
    
    public function changeDealStatus(ChangeDealStatusCommand $command): void {
        DB::transaction(function () use ($command) {
            $deal = $this->dealRepository->findById($command->dealId);
            if (!$deal) {
                throw new DealNotFoundException($command->dealId);
            }
            
            // Бизнес-логика в Entity
            match ($command->newStatus) {
                DealStatus::CONFIRMED => $deal->confirm(),
                DealStatus::CANCELLED => $deal->cancel($command->reason ?? 'Без причины'),
                default => throw new InvalidDealTransitionException("Неизвестный статус: {$command->newStatus->value}")
            };
            
            $this->dealRepository->save($deal);
            
            // Отправка событий
            foreach ($deal->getRecordedEvents() as $event) {
                $this->eventDispatcher->dispatch($event);
            }
            $deal->clearRecordedEvents();
        });
    }
}
```

---

### **🔧 День 4: Infrastructure Layer**

#### 4.1 Создаем Repository интерфейс и реализацию
```php
// app/Domain/Deal/Contracts/DealRepositoryInterface.php
interface DealRepositoryInterface {
    public function save(Deal $deal): void;
    public function findById(DealId $id): ?Deal;
    public function findByUser(UserId $userId): DealCollection;
    public function nextId(): DealId;
}

// app/Infrastructure/Deal/Repositories/EloquentDealRepository.php
class EloquentDealRepository implements DealRepositoryInterface {
    public function save(Deal $deal): void {
        $model = DealModel::find($deal->getId()->toInt()) ?? new DealModel();
        
        $model->fill([
            'id' => $deal->getId()->toInt(),
            'ad_id' => $deal->getAdId()->toInt(),
            'buyer_id' => $deal->getBuyerId()->toInt(),
            'seller_id' => $deal->getSellerId()->toInt(),
            'price' => $deal->getPrice()->toRubles(),
            'status' => $deal->getStatus()->value,
            'notes' => $deal->getNotes()?->toString(),
            'on_chain_id' => $deal->getOnChainId(),
            'signed_at' => $deal->getSignedAt(),
        ]);
        
        $model->save();
    }
    
    public function findById(DealId $id): ?Deal {
        $model = DealModel::find($id->toInt());
        
        if (!$model) {
            return null;
        }
        
        return $this->toDomain($model);
    }
    
    public function nextId(): DealId {
        return DealId::fromInt(
            DealModel::max('id') + 1
        );
    }
    
    private function toDomain(DealModel $model): Deal {
        return Deal::reconstruct(
            id: DealId::fromInt($model->id),
            adId: AdId::fromInt($model->ad_id),
            buyerId: UserId::fromInt($model->buyer_id),
            sellerId: UserId::fromInt($model->seller_id),
            price: Money::fromRubles($model->price),
            status: DealStatus::from($model->status),
            notes: DealNotes::fromString($model->notes),
            onChainId: $model->on_chain_id,
            signedAt: $model->signed_at ? new \DateTimeImmutable($model->signed_at) : null
        );
    }
}
```

#### 4.2 Создаем сервис для блокчейн интеграции
```php
// app/Domain/Deal/Contracts/BlockchainServiceInterface.php
interface BlockchainServiceInterface {
    public function sendDeal(Deal $deal): void;
}

// app/Infrastructure/Deal/Services/BlockchainHttpService.php
class BlockchainHttpService implements BlockchainServiceInterface {
    public function sendDeal(Deal $deal): void {
        try {
            Http::timeout(10)->post(config('app.blockchain_api_url') . '/save-deal', [
                'deal_id' => $deal->getId()->toInt(),
                'unique_id' => $deal->getUuid(), // Нужно добавить в Entity
                'ad_id' => $deal->getAdId()->toInt(),
                'buyer_id' => $deal->getBuyerId()->toInt(),
                'seller_id' => $deal->getSellerId()->toInt(),
                'price' => $deal->getPrice()->toRubles(),
                'status' => $deal->getStatus()->value,
                'notes' => $deal->getNotes()?->toString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send deal to blockchain-transport', [
                'error' => $e->getMessage(),
                'deal_id' => $deal->getId()->toInt(),
            ]);
            throw new BlockchainServiceException('Ошибка отправки в блокчейн: ' . $e->getMessage());
        }
    }
}
```

---

### **📡 День 5: События и интеграция**

#### 5.1 Создаем обработчики доменных событий
```php
// app/Infrastructure/Deal/EventHandlers/SendDealToBlockchainHandler.php
class SendDealToBlockchainHandler {
    public function __construct(
        private BlockchainServiceInterface $blockchainService
    ) {}
    
    public function handle(DealWasCreated $event): void {
        // Получаем полные данные сделки из репозитория
        $deal = app(DealRepositoryInterface::class)->findById($event->dealId);
        
        if ($deal) {
            $this->blockchainService->sendDeal($deal);
        }
    }
    
    public function handleStatusChange(DealStatusWasChanged $event): void {
        // При смене статуса тоже отправляем в блокчейн
        $deal = app(DealRepositoryInterface::class)->findById($event->dealId);
        
        if ($deal) {
            $this->blockchainService->sendDeal($deal);
        }
    }
}
```

#### 5.2 Обновляем контроллер
```php
// app/Http/Controllers/DealController.php (обновляем)
class DealController extends Controller {
    public function __construct(
        private DealApplicationService $dealService
    ) {}
    
    public function store(CreateDealRequest $request) {
        $dealId = $this->dealService->createDeal(
            new CreateDealCommand(
                adId: AdId::fromInt($request->ad_id),
                buyerId: UserId::fromInt($request->user()->id),
                price: Money::fromRubles($request->price),
                notes: DealNotes::fromString($request->notes),
                documents: $request->file('documents') ?? []
            )
        );
        
        return response()->json(['id' => $dealId->toInt()]);
    }
    
    public function updateStatus(Deal $deal, UpdateDealStatusRequest $request) {
        $this->dealService->changeDealStatus(
            new ChangeDealStatusCommand(
                dealId: DealId::fromInt($deal->id),
                newStatus: DealStatus::from($request->status),
                reason: $request->reason
            )
        );
        
        return response()->json(['success' => true]);
    }
}
```

---

## ✅ **Результат после рефакторинга:**

1. **✅ Чистая бизнес-логика** - правила переходов статусов в Entity
2. **✅ Типобезопасность** - нельзя передать неправильный ID или отрицательную цену  
3. **✅ Тестируемость** - можно тестировать логику без БД
4. **✅ Расширяемость** - легко добавлять новые статусы и правила
5. **✅ Интеграция с микросервисом** - через интерфейс, легко заменить

## 🚀 **С чего начинаем завтра?**

Хотите начать с создания Value Objects (Money, DealId) или сразу с Domain Entity? Какой подход вам ближе?
