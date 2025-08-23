# 🚀 План миграции на DDD архитектуру

## 📝 Что у вас уже есть хорошего

1. **✅ DTO слой** - ваши `DTO/` уже структурированы и используют `spatie/laravel-data`
2. **✅ Разделенные сервисы** - `AdService`, `DealService`, `ThreadService` уже выделены
3. **✅ События** - используете `DealCreatedOrUpdatedEvent`
4. **✅ Enums** - `DealStatusEnum`, `AdCategoryEnum` уже созданы
5. **✅ Трейты** - `HasUuid` для доменной идентификации

## 📅 Поэтапный план (5 недель)

---

## 🔥 **Неделя 1: Value Objects и исключения**

### Что делаем:
Создаем основные Value Objects для защиты от некорректных данных.

### Задачи:

#### 1.1 Создать базовые Value Objects
```php
// Уже создан: app/Shared/ValueObjects/Inn.php ✅

// Создать:
app/Shared/ValueObjects/Money.php          # Деньги с валютой
app/Shared/ValueObjects/Email.php          # Email с валидацией  
app/Shared/ValueObjects/Phone.php          # Телефон с валидацией
```

#### 1.2 Создать доменные Value Objects
```php
app/Domain/Company/ValueObjects/CompanyName.php    # Название компании
app/Domain/Ad/ValueObjects/AdTitle.php             # Заголовок объявления
app/Domain/Ad/ValueObjects/AdDescription.php       # Описание с лимитами
app/Domain/Deal/ValueObjects/DealNotes.php         # Заметки к сделке
```

#### 1.3 Добавить исключения
```php
app/Shared/Exceptions/InvalidMoneyException.php
app/Domain/Company/Exceptions/InvalidCompanyException.php
app/Domain/Deal/Exceptions/DealCannotBeCreatedException.php
```

#### 1.4 Обновить существующие модели
Начать использовать Value Objects в текущих моделях:

```php
// В Models/Company.php добавить методы:
public function getInn(): Inn {
    return Inn::fromString($this->inn);
}

public function getValidatedEmail(): Email {
    return Email::fromString($this->email);  
}
```

**Результат недели:** У вас есть валидированные Value Objects, которые можно использовать в текущем коде.

---

## 🏗️ **Неделя 2: Domain Entities**

### Что делаем:
Извлекаем бизнес-логику из моделей в Domain Entities.

### Задачи:

#### 2.1 Создать Domain Entities
```php
// Domain/Company/Entities/Company.php
class Company {
    private CompanyId $id;
    private Inn $inn;
    private CompanyName $name;
    private Email $email;
    private VerificationStatus $status;
    
    // Перенести сюда методы из Models/Company.php:
    public function needsAiUpdate(): bool { ... }
    public function verify(): void { ... }
    public function isReliable(): bool { ... }
}

// Domain/Deal/Entities/Deal.php  
class Deal {
    private DealId $id;
    private AdId $adId;
    private UserId $buyerId;
    private UserId $sellerId;
    private Money $price;
    private DealStatus $status;
    
    // Перенести бизнес-методы:
    public function canBeConfirmed(): bool { ... }
    public function confirm(): void { ... }
    public function cancel(string $reason): void { ... }
}

// Domain/Ad/Entities/Ad.php
class Ad {
    private AdId $id;
    private UserId $userId;
    private AdTitle $title;
    private AdDescription $description;
    private Money $price;
    private AdStatus $status;
    
    // Бизнес-логика публикации, редактирования
    public function publish(): void { ... }
    public function canBeEdited(): bool { ... }
}
```

#### 2.2 Обновить Eloquent модели
Удалить бизнес-логику из моделей, оставить только данные:

```php
// Models/Company.php (станет простой моделью данных)
class Company extends Model {
    protected $fillable = ['inn', 'name', 'email', ...];
    
    // Только отношения, без бизнес-логики
    public function users(): HasMany { ... }
}
```

**Результат недели:** Бизнес-логика отделена от данных. Domain entities содержат правила бизнеса.

---

## 🗂️ **Неделя 3: Repository Pattern**

### Что делаем:
Создаем интерфейсы репозиториев и их реализации.

### Задачи:

#### 3.1 Создать интерфейсы репозиториев
```php
// Domain/Company/Contracts/CompanyRepositoryInterface.php
interface CompanyRepositoryInterface {
    public function save(Company $company): void;
    public function findById(CompanyId $id): ?Company;
    public function findByInn(Inn $inn): ?Company;
    public function findNeedingVerification(): CompanyCollection;
}

// Domain/Deal/Contracts/DealRepositoryInterface.php
interface DealRepositoryInterface {
    public function save(Deal $deal): void;
    public function findById(DealId $id): ?Deal;
    public function findByUser(UserId $userId): DealCollection;
}

// Domain/Ad/Contracts/AdRepositoryInterface.php
interface AdRepositoryInterface {
    public function save(Ad $ad): void;
    public function findById(AdId $id): ?Ad;
    public function findPublished(AdFilters $filters): AdCollection;
}
```

#### 3.2 Создать Eloquent реализации
```php
// Infrastructure/Company/Repositories/EloquentCompanyRepository.php
class EloquentCompanyRepository implements CompanyRepositoryInterface {
    public function save(Company $company): void {
        // Преобразование Domain entity в Eloquent модель
    }
    
    public function findByInn(Inn $inn): ?Company {
        $model = CompanyModel::where('inn', $inn->toString())->first();
        return $model ? $this->toDomain($model) : null;
    }
    
    private function toDomain(CompanyModel $model): Company {
        // Преобразование Eloquent модели в Domain entity
    }
}
```

#### 3.3 Настроить Service Provider
```php
// В AppServiceProvider::register()
$this->app->bind(
    CompanyRepositoryInterface::class,
    EloquentCompanyRepository::class
);
```

**Результат недели:** Данные отделены от домена через Repository pattern.

---

## 🎼 **Неделя 4: Application Services**

### Что делаем:
Разбиваем ваши существующие сервисы на Application и Domain слои.

### Задачи:

#### 4.1 Создать Application Services
Заменить ваши текущие сервисы:

```php
// Было: Services/Deal/DealService.php
// Станет: Application/Deal/Services/DealApplicationService.php
class DealApplicationService {
    public function createDeal(CreateDealCommand $command): DealId {
        return DB::transaction(function () use ($command) {
            $ad = $this->adRepository->findById($command->adId);
            $buyer = $this->userRepository->findById($command->buyerId);
            
            $deal = Deal::create($ad, $buyer, $command->notes);
            
            $this->dealRepository->save($deal);
            
            return $deal->getId();
        });
    }
}

// Application/Company/Services/CompanyApplicationService.php  
class CompanyApplicationService {
    public function verifyCompany(CompanyId $companyId): void {
        $company = $this->companyRepository->findById($companyId);
        
        // Вызываем доменный сервис
        $this->verificationService->verify($company);
        
        $this->companyRepository->save($company);
    }
}
```

#### 4.2 Создать Commands и Queries
Заменить ваши DTO на Commands/Queries:

```php
// Application/Deal/Commands/CreateDealCommand.php
final class CreateDealCommand {
    public function __construct(
        public readonly AdId $adId,
        public readonly UserId $buyerId,
        public readonly ?string $notes = null,
        public readonly array $documents = []
    ) {}
}

// Application/Company/Queries/GetCompaniesQuery.php
final class GetCompaniesQuery {
    public function __construct(
        public readonly ?string $searchTerm = null,
        public readonly ?VerificationStatus $status = null
    ) {}
}
```

#### 4.3 Обновить контроллеры
```php
// Http/Controllers/DealController.php
class DealController {
    public function store(CreateDealRequest $request) {
        $dealId = $this->dealService->createDeal(
            new CreateDealCommand(
                adId: AdId::fromInt($request->ad_id),
                buyerId: UserId::fromInt($request->user()->id),
                notes: $request->notes
            )
        );
        
        return response()->json(['id' => $dealId->toString()]);
    }
}
```

**Результат недели:** Четкое разделение координации (Application) и бизнес-логики (Domain).

---

## 🎯 **Неделя 5: События и финализация**

### Что делаем:
Переводим события на доменные, тестируем, документируем.

### Задачи:

#### 5.1 Создать доменные события
```php
// Domain/Deal/Events/DealWasCreated.php
final class DealWasCreated {
    public function __construct(
        public readonly DealId $dealId,
        public readonly AdId $adId,
        public readonly UserId $buyerId,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}

// Domain/Company/Events/CompanyWasVerified.php
final class CompanyWasVerified {
    public function __construct(
        public readonly CompanyId $companyId,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
```

#### 5.2 Обновить Entity для событий
```php
// В Domain entities добавить:
class Deal {
    private array $recordedEvents = [];
    
    public function confirm(): void {
        // бизнес-логика
        
        $this->recordThat(new DealWasConfirmed($this->id, now()));
    }
    
    private function recordThat(object $event): void {
        $this->recordedEvents[] = $event;
    }
    
    public function getRecordedEvents(): array {
        return $this->recordedEvents;
    }
}
```

#### 5.3 Написать тесты
```php
// tests/Unit/Domain/Company/CompanyTest.php
class CompanyTest extends TestCase {
    public function test_company_can_be_verified() {
        $company = Company::create(
            Inn::fromString('1234567890'),
            CompanyName::fromString('Тест ООО')
        );
        
        $company->verify();
        
        $this->assertTrue($company->isVerified());
    }
}
```

**Результат недели:** Полностью работающая DDD архитектура с тестами.

---

## 📋 Чек-лист миграции

### До начала:
- [ ] Сделать backup базы данных
- [ ] Создать ветку `feature/ddd-migration`  
- [ ] Прочитать всю документацию

### После каждой недели:
- [ ] Запустить тесты: `php artisan test`
- [ ] Проверить, что все работает в браузере
- [ ] Commit и push изменений
- [ ] Обновить README с изменениями

### По завершении:
- [ ] Code review всех изменений
- [ ] Обновить документацию API
- [ ] Обучить команду новой архитектуре
- [ ] Deploy на staging для тестирования

---

## 🎯 Ключевые принципы

1. **Постепенность** - мигрируем по одному домену за раз
2. **Обратная совместимость** - старый код продолжает работать
3. **Тестирование** - после каждого шага проверяем работоспособность
4. **Рефакторинг** - не добавляем новый функционал во время миграции

---

## ❓ Частые вопросы

**Q: Можно ли использовать Eloquent в Domain слое?**
A: Нет! Domain должен быть независим от фреймворка. Используйте репозитории.

**Q: Где размещать валидацию?**
A: Бизнес-валидация - в Domain, HTTP-валидация - в Request классах.

**Q: Как тестировать Domain без БД?**
A: Используйте моки для репозиториев и работайте с чистыми объектами.

**Q: Нужно ли переписывать всё сразу?**
A: Нет! Начните с одного домена (рекомендую Company) и постепенно расширяйте.

---

## 🚀 После завершения у вас будет:

- ✅ Чистая архитектура с четким разделением слоев
- ✅ Высокое покрытие тестами Domain слоя  
- ✅ Легкость добавления нового функционала
- ✅ Независимость от фреймворка в бизнес-логике
- ✅ Понятная структура для новых разработчиков

**Начинайте с малого, двигайтесь постепенно, не бойтесь рефакторинга!** 🎉
