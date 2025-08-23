# 🔄 Полный флоу DDD архитектуры для Deal

## 🎯 Создание сделки (POST /deals)

### 1. **HTTP Request** → **Controller**
```http
POST /deals/store/123
Content-Type: application/json
{
  "notes": "Хочу купить этот товар"
}
```

### 2. **Controller** → **Command DTO**
```php
// DealController::store()
$command = CreateDealCommand::fromRequest($request, $ad, auth()->id());
```
**Что происходит:**
- Валидированные данные из Request
- Добавляем контекст (Ad, User ID) 
- Создаем immutable Command объект

### 3. **Controller** → **Use Case**
```php
$deal = $createDealUseCase->execute($command);
```
**Что происходит:**
- Dependency Injection находит CreateDealUseCase
- Передаем Command в Use Case

### 4. **Use Case** → **Domain Layer**
```php
// CreateDealUseCase::execute()
$this->validateCommand($command);           // Бизнес-валидация
$dealId = $this->dealRepository->nextId();  // Генерация ID
$deal = Deal::create(...);                  // Создание Entity
```
**Что происходит:**
- Проверяем бизнес-правила (покупатель ≠ продавец)
- Преобразуем примитивы в Value Objects
- Создаем доменную сущность Deal
- **Domain Event записывается:** `DealWasCreated`

### 5. **Use Case** → **Repository**
```php
$this->dealRepository->save($deal);
```
**Что происходит:**
- Находим EloquentDealRepository через DI
- Преобразуем Domain Entity → Infrastructure Model
- Сохраняем в БД через Eloquent

### 6. **Repository** → **Database**  
```php
// EloquentDealRepository::save()
$model->fill([
    'id' => $deal->getId()->toInt(),
    'price' => $deal->getPrice()->toRubles(),
    'status' => $deal->getStatus()->value,
    'uuid' => $deal->getUuid(),
    // ...
]);
$model->save();
```
**Что происходит:**
- Domain Entity → Infrastructure Model 
- Value Objects → примитивные типы
- Eloquent → SQL INSERT

### 7. **Use Case** → **Event Dispatcher**
```php
foreach ($deal->getRecordedEvents() as $domainEvent) {
    Event::dispatch($domainEvent);
}
```
**Что происходит:**
- Получаем записанные Domain Events
- Отправляем каждое событие через Laravel Event System

### 8. **Event System** → **Listeners**
```php
// DealServiceProvider::boot()
Event::listen(DealWasCreated::class, SendNewDealToBlockchain::class);
```
**Что происходит:**
- Laravel находит зарегистрированный Listener  
- Вызывает `SendNewDealToBlockchain::handle()`

### 9. **Listener** → **Микросервис**
```php
// SendNewDealToBlockchain::handle()
Http::post(config('app.blockchain_api_url') . '/save-deal', $payload);
```
**Что происходит:**
- Преобразуем Domain Event → API payload
- Отправляем HTTP запрос в Go микросервис
- Логируем результат (успех/ошибку)

### 10. **Controller** → **Response**
```php
return Inertia::location(route('user.ads.show', [...]);
```
**Что происходит:**
- Успешный redirect на страницу объявления
- Сделка создана в БД + отправлена в микросервис

---

## 🔄 Изменение статуса сделки (PATCH /deals/123/status)

### 1. **HTTP Request** → **Controller**
```http
PATCH /deals/123/status
{
  "status": "accepted",
  "reason": "Согласен на эту цену"
}
```

### 2. **Controller** → **Command DTO** 
```php
$command = ChangeDealStatusCommand::fromRequest($request, $deal->id, auth()->id());
```

### 3. **Use Case** → **Repository** (загрузка)
```php
$deal = $this->dealRepository->findById($dealId);
```
**Что происходит:**
- SQL SELECT
- Infrastructure Model → Domain Entity
- Восстанавливаем все Value Objects

### 4. **Use Case** → **Domain Entity** (бизнес-логика)
```php
$deal->accept(); // или reject(), complete(), cancel()
```
**Что происходит:**
- Проверка возможности перехода: `DealStatus::canTransitionTo()`
- Изменение статуса с валидацией
- **Domain Event записывается:** `DealStatusWasChanged`

### 5. **Use Case** → **Repository** (сохранение)
```php
$this->dealRepository->save($deal);
```

### 6. **Use Case** → **Event Dispatcher**
- Аналогично п.7 из создания сделки

### 7. **Controller** → **Response**
```php
return to_route('user.deals.index')->with(['success' => '...']);
```

---

## 📊 Получение сделок пользователя (GET /deals)

### 1. **HTTP Request** → **Controller**
```http
GET /user/deals
```

### 2. **Controller** → **Service** (пока legacy)
```php
$deals = $dealService->getDeals(); // Временно старый подход
```

### Когда переведем на DDD:
```php
$domainDeals = $getUserDealsUseCase->execute(auth()->id());
```
**Что произойдет:**
- Use Case → Repository: `findByUser(UserId)`
- Repository → DB: SQL SELECT с JOIN'ами
- DB → Repository: Infrastructure Models
- Repository → Use Case: Domain Entities массив
- Use Case → Controller: Domain Entities
- Controller → Frontend: преобразование в массивы

---

## 🎯 Ключевые принципы флоу:

### 📥 **Входящий поток данных:**
```
HTTP Request → Controller → Command DTO → Use Case → Domain Entity → Repository → Database
```

### 📤 **Исходящий поток данных:**
```  
Database → Repository → Domain Entity → Use Case → Controller → HTTP Response
```

### 🔔 **Побочные эффекты (Events):**
```
Domain Entity → Domain Event → Event Dispatcher → Listeners → External Systems
```

### 🛡️ **Слои защиты:**
1. **HTTP Validation** - в Request classes
2. **Business Validation** - в Use Cases  
3. **Domain Validation** - в Value Objects и Entities
4. **Infrastructure Validation** - в Repository/Models

---

## 🔥 Почему это круто:

### 🧪 **Тестируемость:**
- **Unit тесты Domain** - без БД, без HTTP
- **Unit тесты Use Cases** - с mock Repository
- **Feature тесты Controller** - полный интеграционный тест

### 🔧 **Гибкость:**
- Заменить Eloquent → MongoDB: меняем только Repository
- Заменить HTTP → GraphQL: меняем только Controller
- Добавить новый микросервис: добавляем Listener

### 📈 **Масштабируемость:**
- Добавить новую бизнес-операцию: новый Use Case
- Изменить бизнес-правила: меняем только Domain Entity
- Добавить интеграцию: новый Event Listener

### 🛡️ **Надежность:**
- Ошибки микросервиса НЕ ломают создание сделки
- Валидация на каждом слое
- Типобезопасность Value Objects

**Это и есть мощь Clean Architecture + DDD! 🚀**
