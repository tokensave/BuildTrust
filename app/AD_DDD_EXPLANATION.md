# 📚 DDD на примере домена Ad (Объявления)

## 🎯 Ваш текущий код и куда он переместится

### 📁 **Текущая структура:**
```
Models/Ad.php                          # Модель с бизнес-логикой
Http/Controllers/UserAdsController.php  # HTTP контроллер
Http/Requests/StoreAdRequest.php        # Валидация HTTP
Services/Ad/AdService.php               # Сервис со всей логикой
DTO/Ad/StoreAdData.php                 # DTO для передачи данных
Enums/AdEnums/                         # Перечисления
Events/AdPublished.php                 # Laravel события
```

### 🏗️ **После DDD будет:**
```
Domain/Ad/                             # ВСЯ БИЗНЕС-ЛОГИКА
├── Entities/Ad.php                    # Доменная сущность
├── ValueObjects/AdTitle.php           # Заголовок с валидацией
├── ValueObjects/AdDescription.php     # Описание с лимитами
├── Services/AdValidationService.php   # Бизнес-правила валидации
├── Events/AdWasPublished.php         # Доменное событие
├── Contracts/AdRepositoryInterface.php # Интерфейс репозитория
└── Exceptions/AdCannotBePublishedException.php

Application/Ad/                        # КООРДИНАЦИЯ
├── Services/AdApplicationService.php  # Координирует операции
├── Commands/CreateAdCommand.php       # Команда создания
├── Commands/UpdateAdCommand.php       # Команда обновления
├── Queries/GetUserAdsQuery.php       # Запрос объявлений пользователя
└── Handlers/CreateAdCommandHandler.php

Infrastructure/Ad/                     # ТЕХНИЧЕСКАЯ РЕАЛИЗАЦИЯ
├── Models/AdModel.php                # Чистая Eloquent модель
├── Repositories/EloquentAdRepository.php # Работа с БД
└── Services/MediaUploadService.php   # Загрузка файлов

Http/Controllers/UserAdsController.php # Остается, но упрощается
Http/Requests/StoreAdRequest.php       # Остается (HTTP валидация)
```

---

## 🔍 **Разбор по слоям - куда что идет**

### 1. **🏛️ DOMAIN LAYER - Бизнес-логика**

#### **Entities/Ad.php** - Главная бизнес-сущность
**Что туда идет:**
```php
// ИЗ Models/Ad.php ПЕРЕНОСИМ бизнес-методы:
public function getIsServiceAttribute(): bool          → Ad->isService(): bool
public function getFormattedCategoryAttribute()       → Ad->getFormattedCategory(): string  
public function canBePublished(): bool                → НОВЫЙ бизнес-метод
public function publish(): void                       → НОВЫЙ бизнес-метод
public function canBeEdited(): bool                   → НОВЫЙ бизнес-метод

// ИЗ AdService.php ПЕРЕНОСИМ бизнес-правила:
public function checkDeal(Ad $ad): bool               → Ad->hasActiveDeals(): bool
```

**Что НЕ идет:**
- ❌ Eloquent отношения (`belongsTo`, `hasMany`)
- ❌ `protected $fillable`, `protected $casts`
- ❌ Методы для работы с БД
- ❌ Scopes (`scopeFiltered`, `scopeByType`)

#### **ValueObjects/** - Валидированные значения
**Что создаем НОВОЕ:**
```php
Domain/Ad/ValueObjects/AdTitle.php        # Заголовок 3-100 символов
Domain/Ad/ValueObjects/AdDescription.php  # Описание до 5000 символов  
Domain/Ad/ValueObjects/AdLocation.php     # Локация с форматированием
```

**Откуда берем данные:**
- Из вашего `StoreAdData->title` → `AdTitle`
- Из вашего `StoreAdData->description` → `AdDescription`
- Из Shared VO: цена → `Money`, пользователь → `UserId`

#### **Events/** - Доменные события
**Что создаем:**
```php
Domain/Ad/Events/AdWasCreated.php         # Вместо Laravel Event
Domain/Ad/Events/AdWasPublished.php       # Доменное событие
Domain/Ad/Events/AdWasArchived.php        # При удалении
```

**Отличие от Laravel Events:**
- ✅ Доменные события - часть бизнес-логики
- ✅ Описывают что ПРОИЗОШЛО в бизнесе
- ✅ Не знают о Laravel, HTTP, БД

---

### 2. **🎼 APPLICATION LAYER - Координация**

#### **Services/AdApplicationService.php** - Оркестратор
**Что туда идет из AdService.php:**
```php
// Координация операций, но БЕЗ бизнес-логики:

ИЗ AdService->create()     → createAd(CreateAdCommand): AdId
ИЗ AdService->update()     → updateAd(UpdateAdCommand): void  
ИЗ AdService->delete()     → deleteAd(AdId): void
ИЗ AdService->getUserAds() → НЕТ - это Query, не команда

// Управление транзакциями:
DB::transaction() - ОСТАЕТСЯ здесь
```

#### **Commands/** - Команды изменения состояния
**Что создаем из DTO:**
```php
// DTO/Ad/StoreAdData.php → Application/Ad/Commands/CreateAdCommand.php
CreateAdCommand {
    public readonly AdTitle $title;
    public readonly AdDescription $description;  
    public readonly Money $price;
    public readonly UserId $userId;
    public readonly array $imageFiles;
}

// DTO/Ad/UpdateAdData.php → Application/Ad/Commands/UpdateAdCommand.php  
```

#### **Queries/** - Запросы на чтение
**Что создаем НОВОЕ:**
```php
Application/Ad/Queries/GetUserAdsQuery.php     # Из AdService->getUserAds()
Application/Ad/Queries/GetPublishedAdsQuery.php # Из AdService->getPublishedAds()
Application/Ad/Queries/FindAdByIdQuery.php     # Из AdService->findById()
```

---

### 3. **🔧 INFRASTRUCTURE LAYER - Техника**

#### **Models/AdModel.php** - Чистая Eloquent модель
**Что ОСТАЕТСЯ:**
```php
// Только техническая работа с БД:
protected $fillable = [...]              ✅ ОСТАЕТСЯ
protected $casts = [...]                 ✅ ОСТАЕТСЯ  
public function user(): BelongsTo        ✅ ОСТАЕТСЯ
public function deals(): HasMany         ✅ ОСТАЕТСЯ
registerMediaCollections()              ✅ ОСТАЕТСЯ

// Scopes для запросов:
scopeByType(), scopeByCategory()         ✅ ОСТАЕТСЯ
static function filtered()              ✅ ОСТАЕТСЯ (или в Repository)
```

**Что УБИРАЕМ:**
```php  
getIsServiceAttribute()                  → В Domain Entity
getFormattedCategoryAttribute()          → В Domain Entity
```

#### **Repositories/EloquentAdRepository.php** - Работа с данными
**Что туда идет:**
```php
// Методы поиска из AdService:
AdService->findById()                    → findById(AdId): ?Ad
AdService->getUserAds()                  → findByUser(UserId): AdCollection
AdService->getPublishedAds()            → findPublished(filters): AdCollection

// Сохранение:
AdService->create() - ЧАСТЬ с БД         → save(Ad): void
AdService->update() - ЧАСТЬ с БД         → save(Ad): void  
AdService->delete()                      → delete(AdId): void
```

---

### 4. **🌐 HTTP LAYER - Остается как есть (почти)**

#### **Controllers/UserAdsController.php** - Упрощается!
**Что изменится:**
```php
// БЫЛО:
public function store(StoreAdRequest $request, AdService $service) {
    $ad = $service->create($request->user(), StoreAdData::fromRequest($request));
    return redirect()->back();
}

// СТАНЕТ:  
public function store(StoreAdRequest $request) {
    $adId = $this->commandBus->handle(
        new CreateAdCommand(
            title: AdTitle::fromString($request->title),
            description: AdDescription::fromString($request->description),
            price: Money::fromRubles($request->price),
            userId: UserId::fromInt($request->user()->id),
            // ...
        )
    );
    
    return redirect()->back();
}
```

#### **Requests/StoreAdRequest.php** - Остается!
**Что НЕ меняется:**
- ✅ HTTP валидация остается в Request классах
- ✅ `required`, `string`, `max:255` и т.д.
- ✅ Авторизация `authorize()`

**Отличие от доменной валидации:**
- HTTP валидация: "поле обязательно", "максимум 255 символов"
- Доменная валидация: "заголовок не может содержать мат", "цена должна быть больше 0 для платных объявлений"

---

## 🔄 **Поток данных - как все работает вместе**

### **Создание объявления:**

```
1. HTTP Request
   ↓
2. StoreAdRequest (HTTP валидация)
   ↓  
3. UserAdsController->store()
   ↓
4. CreateAdCommand (из Request данных)
   ↓
5. AdApplicationService->createAd(Command)
   ↓
6. Domain/Ad/Entity->create() (бизнес-правила)
   ↓  
7. AdRepository->save(Ad) (сохранение в БД)
   ↓
8. AdWasCreated Event (доменное событие)
```

### **Получение объявлений пользователя:**

```
1. HTTP Request  
   ↓
2. UserAdsController->index()
   ↓
3. GetUserAdsQuery
   ↓
4. AdQueryHandler->handle(Query) 
   ↓
5. AdRepository->findByUser(UserId)
   ↓
6. AdCollection (доменные объекты)
   ↓
7. DTO для фронтенда
```

---

## ❓ **Частые вопросы и путаница**

### **Q: Зачем так сложно? У меня же все работает!**
**A:** Сейчас работает, но:
- Когда добавите "Модерацию объявлений" - куда логику?  
- Когда добавите "Автоматическое продление" - где правила?
- Когда понадобится "API для мобилки" - как переиспользовать?

### **Q: В чем разница между Laravel Event и Domain Event?**
**A:**
```php
// Laravel Event - техническое уведомление:
Event::dispatch(new AdCreated($ad->id));  // Знает про Laravel

// Domain Event - бизнес-событие:
$ad->recordThat(new AdWasCreated($adId));  // Не знает про Laravel
```

### **Q: Где валидация - в Request или Domain?**
**A:** **ВЕЗДЕ, но разная!**
```php  
// HTTP валидация (Request):
'title' => 'required|string|max:255'

// Доменная валидация (ValueObject):
class AdTitle {
    public function __construct(string $title) {
        if (str_contains(strtolower($title), 'продам наркотики')) {
            throw new InvalidAdTitleException('Недопустимый контент');
        }
    }
}
```

### **Q: Зачем Commands и Queries? У меня же есть DTO!**
**A:** **Разные задачи:**
```php
// DTO - просто данные:
StoreAdData { public string $title; }

// Command - НАМЕРЕНИЕ изменить систему:
CreateAdCommand { 
    public readonly AdTitle $title;    // Уже валидированный!
    public readonly UserId $createdBy; // Уже типизированный!
}

// Query - НАМЕРЕНИЕ получить данные:  
GetUserAdsQuery {
    public readonly UserId $userId;
    public readonly ?AdStatus $status;
}
```

---

## 🎯 **Главные преимущества после DDD**

### **1. Чистое тестирование:**
```php
// СЕЙЧАС - тяжело тестировать:
public function test_ad_creation() {
    // Нужна БД, HTTP, файлы...
}

// ПОСЛЕ DDD - легко:
public function test_ad_can_be_published() {
    $ad = new Ad(...);
    $ad->publish();
    $this->assertTrue($ad->isPublished());
}
```

### **2. Переиспользование логики:**
```php
// Одна и та же бизнес-логика в:
- Web контроллере
- API контроллере  
- Console команде
- Queue job
```

### **3. Ясность кода:**
```php
// СЕЙЧАС - непонятно где что:
AdService->create() // 50 строк всего вперемешку

// ПОСЛЕ DDD - каждый класс делает одно:
CreateAdCommand      // Данные для создания
AdApplicationService // Координация
Ad Entity           // Бизнес-правила
AdRepository        // Работа с БД
```

---

## 🚀 **С чего начать миграцию Ad домена**

### **Неделя 1: Value Objects**
1. Создать `AdTitle`, `AdDescription` 
2. Обновить `Models/Ad.php` чтобы использовать их

### **Неделя 2: Domain Entity**  
1. Создать `Domain/Ad/Entities/Ad.php`
2. Перенести бизнес-методы из модели

### **Неделя 3: Repository**
1. Создать `AdRepositoryInterface`
2. Реализовать `EloquentAdRepository`

### **Неделя 4: Application Services**
1. Разбить `AdService` на Application и Domain части
2. Создать Commands и Queries

### **Неделя 5: События и тесты**
1. Заменить Laravel Events на Domain Events
2. Написать тесты для Domain слоя

**Результат:** У вас будет чистая, тестируемая, расширяемая архитектура! 🎉
