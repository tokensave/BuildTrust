# 🎼 Application Layer - Слой приложения

## 📝 Назначение
**Оркестратор** - координирует работу между доменами, но сам не содержит бизнес-логику. Это "дирижер оркестра", который управляет доменными сервисами.

## 📁 Структура папок

### **Services/** - Application Services
**Что здесь:** Сервисы приложения, которые координируют выполнение бизнес-операций.

**Принципы:**
- ✅ Координируют работу между доменами
- ✅ Управляют транзакциями
- ✅ Обрабатывают события
- ❌ НЕ содержат бизнес-логику
- ❌ НЕ знают о деталях реализации (БД, HTTP)

**Пример:**
```php
// Application/Company/Services/CompanyApplicationService.php
class CompanyApplicationService {
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private CompanyVerificationService $verificationService,
        private EventDispatcherInterface $eventDispatcher
    ) {}
    
    public function verifyCompany(VerifyCompanyCommand $command): void {
        DB::transaction(function () use ($command) {
            // 1. Получить домен объект
            $company = $this->companyRepository->findById($command->companyId);
            
            if (!$company) {
                throw new CompanyNotFoundException($command->companyId);
            }
            
            // 2. Выполнить доменную операцию
            $company->verify();
            
            // 3. Сохранить изменения
            $this->companyRepository->save($company);
            
            // 4. Обработать события
            foreach ($company->getRecordedEvents() as $event) {
                $this->eventDispatcher->dispatch($event);
            }
        });
    }
}
```

### **Commands/** - Команды
**Что здесь:** Объекты, представляющие намерение изменить состояние системы.

**Принципы:**
- ✅ Неизменяемые объекты (DTO)
- ✅ Содержат данные для выполнения операции
- ✅ Валидируются на входе
- ✅ Именуются глаголами в повелительном наклонении

**Пример:**
```php
// Application/Company/Commands/CreateCompanyCommand.php
final class CreateCompanyCommand {
    public function __construct(
        public readonly string $inn,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly UserId $createdBy
    ) {}
}

// Application/Deal/Commands/CreateDealCommand.php  
final class CreateDealCommand {
    public function __construct(
        public readonly AdId $adId,
        public readonly UserId $buyerId,
        public readonly Money $price,
        public readonly ?string $notes = null,
        public readonly array $documents = []
    ) {}
}
```

### **Queries/** - Запросы
**Что здесь:** Объекты для получения данных без изменения состояния.

**Принципы:**
- ✅ Только для чтения данных
- ✅ Могут содержать фильтры и пагинацию
- ✅ НЕ изменяют состояние системы
- ✅ Именуются существительными

**Пример:**
```php
// Application/Company/Queries/GetCompaniesQuery.php
final class GetCompaniesQuery {
    public function __construct(
        public readonly ?string $searchTerm = null,
        public readonly ?VerificationStatus $status = null,
        public readonly int $page = 1,
        public readonly int $perPage = 20
    ) {}
}

// Application/Ad/Queries/GetUserAdsQuery.php
final class GetUserAdsQuery {
    public function __construct(
        public readonly UserId $userId,
        public readonly ?AdCategory $category = null,
        public readonly ?AdStatus $status = null
    ) {}
}
```

### **Handlers/** - Обработчики
**Что здесь:** Обработчики команд, запросов и событий.

**Принципы:**
- ✅ Каждый handler обрабатывает один тип команды/запроса
- ✅ Содержат минимум логики (только координацию)
- ✅ Используют репозитории и доменные сервисы

**Пример:**
```php
// Application/Company/Handlers/CreateCompanyCommandHandler.php
class CreateCompanyCommandHandler {
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private CompanyFactoryInterface $companyFactory
    ) {}
    
    public function handle(CreateCompanyCommand $command): CompanyId {
        // 1. Создать доменный объект через фабрику
        $company = $this->companyFactory->create(
            Inn::fromString($command->inn),
            CompanyName::fromString($command->name),
            Email::fromString($command->email),
            Phone::fromString($command->phone)
        );
        
        // 2. Сохранить
        $this->companyRepository->save($company);
        
        return $company->getId();
    }
}

// Application/Company/Handlers/GetCompaniesQueryHandler.php
class GetCompaniesQueryHandler {
    public function __construct(
        private CompanyRepositoryInterface $companyRepository
    ) {}
    
    public function handle(GetCompaniesQuery $query): CompanyCollection {
        return $this->companyRepository->findByFilters(
            searchTerm: $query->searchTerm,
            status: $query->status,
            pagination: new Pagination($query->page, $query->perPage)
        );
    }
}
```

## 🔄 Паттерны Application слоя

### **CQRS (Command Query Responsibility Segregation)**
Разделяем команды (изменяют состояние) и запросы (читают данные):

```php
// Команда - изменяет состояние
$commandBus->handle(new CreateDealCommand($adId, $buyerId, $price));

// Запрос - только читает
$deals = $queryBus->handle(new GetUserDealsQuery($userId));
```

### **Command Bus / Query Bus**
Централизованная обработка команд и запросов:

```php
// В контроллере
class CompanyController {
    public function store(CreateCompanyRequest $request) {
        $companyId = $this->commandBus->handle(
            new CreateCompanyCommand(
                inn: $request->inn,
                name: $request->name,
                email: $request->email,
                phone: $request->phone,
                createdBy: $request->user()->id
            )
        );
        
        return response()->json(['id' => $companyId]);
    }
}
```

## 📊 Примеры из вашего проекта

### До DDD:
```php
// Services/Deal/DealService.php
class DealService {
    public function createDeal(StoreDealData $data, Ad $ad): Deal {
        return DB::transaction(static function () use ($data, $ad) {
            // 20+ строк смешанной логики
            $deal = Deal::create([...]);
            
            if (!empty($data->documents)) {
                foreach ($data->documents as $file) {
                    $deal->addMedia($file)->toMediaCollection('documents');
                }
            }
            
            event(new DealCreatedOrUpdatedEvent($deal));
            return $deal;
        });
    }
}
```

### После DDD:
```php
// Application/Deal/Services/DealApplicationService.php
class DealApplicationService {
    public function createDeal(CreateDealCommand $command): DealId {
        return DB::transaction(function () use ($command) {
            // 1. Получить объявление
            $ad = $this->adRepository->findById($command->adId);
            
            // 2. Создать сделку через доменный сервис
            $deal = $this->dealFactory->createFromAd(
                $ad, 
                $command->buyerId, 
                $command->notes
            );
            
            // 3. Добавить документы через доменный сервис
            $this->documentService->attachDocuments($deal, $command->documents);
            
            // 4. Сохранить
            $this->dealRepository->save($deal);
            
            return $deal->getId();
        });
    }
}
```

## 🔧 Правила Application слоя

### ✅ Что МОЖНО делать:
- Координировать работу доменных сервисов
- Управлять транзакциями
- Обрабатывать команды и запросы
- Диспетчеризировать события
- Валидировать входящие данные

### ❌ Что НЕЛЬЗЯ делать:
- Содержать бизнес-логику
- Работать с базой данных напрямую (через Eloquent)
- Знать о деталях HTTP (Request, Response)
- Дублировать логику из Domain слоя

## 🎯 Что нужно сделать с вашим кодом

### 1. **Разбить текущие сервисы**
```php
// Было:
Services/Deal/DealService.php (100+ строк)

// Станет:
Application/Deal/Services/DealApplicationService.php (координация)
Domain/Deal/Services/DealCreationService.php (бизнес-логика)
Infrastructure/Deal/Repositories/EloquentDealRepository.php (данные)
```

### 2. **Создать Commands/Queries**
```php
// Заменить ваши DTO на команды:
DTO/Deal/StoreDealData.php → Application/Deal/Commands/CreateDealCommand.php

// Добавить запросы для чтения:
Application/Deal/Queries/GetUserDealsQuery.php
```

### 3. **Вынести координацию из контроллеров**
```php
// Было в контроллере:
public function store() {
    $deal = $this->dealService->createDeal($data, $ad);
    // логика обработки
}

// Станет:
public function store() {
    $dealId = $this->commandBus->handle(new CreateDealCommand(...));
    return response()->json(['id' => $dealId]);
}
```

---

## 🚀 Преимущества Application слоя

1. **Тестируемость** - легко мокать зависимости
2. **CQRS** - четкое разделение чтения и записи  
3. **Транзакции** - централизованное управление
4. **Обработка событий** - слабая связанность компонентов
5. **Валидация** - единое место для проверок

Application слой - это **клей**, который связывает чистые домены с грязной инфраструктурой!
