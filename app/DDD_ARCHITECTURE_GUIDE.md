# 📚 DDD Architecture Guide - Руководство по Domain-Driven Design

## 🎯 Что такое DDD и зачем нам это нужно?

**Domain-Driven Design (DDD)** - это подход к разработке сложных программных систем, который фокусируется на моделировании бизнес-логики в виде доменов.

### Проблемы, которые решает DDD в вашем проекте:

1. **Сложность растет** - у вас уже есть компании, объявления, сделки, чат, ИИ-анализ
2. **Логика размазана** - бизнес-правила находятся в контроллерах, моделях, сервисах
3. **Сложно тестировать** - много зависимостей, тяжело изолировать логику
4. **Сложно понимать** - новому разработчику сложно разобраться где что лежит

---

## 🏗️ Структура DDD в вашем проекте

### 📁 Domain/ - Ядро бизнес-логики

**Это самый важный слой! Здесь живет ваша бизнес-логика.**

```
Domain/
├── Company/            # Домен "Компании"
│   ├── Entities/       # Бизнес-сущности
│   ├── ValueObjects/   # Объекты-значения (ИНН, Email)
│   ├── Services/       # Доменные сервисы
│   ├── Events/         # Доменные события
│   ├── Contracts/      # Интерфейсы репозиториев
│   └── Exceptions/     # Исключения домена
```

#### **Entities/** - Доменные сущности
**Что это:** Объекты с уникальной идентичностью, содержащие бизнес-логику.

**Ваши примеры:**
- `Company` - компания с ИНН, именем, статусом верификации
- `Deal` - сделка с состояниями, правилами переходов
- `Ad` - объявление с категориями, статусами

**Что должно быть здесь:**
```php
// Пример: Domain/Company/Entities/Company.php
class Company {
    private CompanyId $id;
    private Inn $inn;
    private CompanyName $name;
    private VerificationStatus $status;
    
    // Бизнес-методы
    public function verify(): void {
        // Логика верификации
    }
    
    public function isReliable(): bool {
        // Бизнес-правило надежности
    }
}
```

#### **ValueObjects/** - Объекты-значения
**Что это:** Неизменяемые объекты, которые характеризуются своим значением, а не идентичностью.

**Ваши примеры:**
- `Inn` - ИНН с валидацией
- `Money` - деньги с валютой
- `Email` - email с валидацией
- `CompanyName` - название компании

**Преимущества:**
- Инкапсулируют валидацию
- Предотвращают ошибки типа "передал цену вместо ID"
- Делают код более читаемым

#### **Services/** - Доменные сервисы
**Что это:** Сервисы, которые содержат бизнес-логику, не принадлежащую конкретной Entity.

**Ваши примеры:**
- `CompanyVerificationService` - логика верификации компаний
- `DealNegotiationService` - логика ведения переговоров
- `FraudDetectionService` - обнаружение мошенничества

#### **Events/** - Доменные события
**Что это:** События, которые происходят в домене и интересны другим частям системы.

**Ваши примеры:**
- `CompanyVerified` - компания прошла верификацию
- `DealCreated` - создана новая сделка
- `AdPublished` - объявление опубликовано

#### **Contracts/** - Интерфейсы
**Что это:** Контракты для репозиториев и внешних сервисов.

**Ваши примеры:**
```php
interface CompanyRepositoryInterface {
    public function save(Company $company): void;
    public function findByInn(Inn $inn): ?Company;
}
```

---

### 📁 Application/ - Слой приложения

**Что это:** Оркестрирует работу доменов, но не содержит бизнес-логику.

```
Application/
├── Company/
│   ├── Services/       # Application Services
│   ├── Commands/       # Команды (изменяют состояние)
│   ├── Queries/        # Запросы (только читают)
│   └── Handlers/       # Обработчики команд/запросов
```

#### **Services/** - Application Services
**Что это:** Координируют работу между доменами, управляют транзакциями.

**Ваш пример:**
```php
// Application/Company/Services/CompanyApplicationService.php
class CompanyApplicationService {
    public function verifyCompany(VerifyCompanyCommand $command): void {
        // 1. Получить компанию из репозитория
        // 2. Вызвать доменный сервис верификации
        // 3. Сохранить изменения
        // 4. Отправить событие
    }
}
```

#### **Commands/** - Команды
**Что это:** Объекты, представляющие намерение изменить состояние системы.

**Ваши примеры:**
- `CreateCompanyCommand`
- `VerifyCompanyCommand`  
- `CreateDealCommand`

#### **Queries/** - Запросы
**Что это:** Объекты для получения данных без изменения состояния.

**Ваши примеры:**
- `GetCompaniesQuery`
- `GetUserAdsQuery`
- `GetDealHistoryQuery`

---

### 📁 Infrastructure/ - Инфраструктурный слой

**Что это:** Техническая реализация - базы данных, внешние API, файлы.

```
Infrastructure/
├── Company/
│   ├── Repositories/   # Реализации репозиториев
│   └── Services/       # Внешние сервисы
```

#### **Repositories/** - Реализации репозиториев
**Что это:** Конкретные реализации интерфейсов из Domain/Contracts.

**Ваш пример:**
```php
// Infrastructure/Company/Repositories/EloquentCompanyRepository.php
class EloquentCompanyRepository implements CompanyRepositoryInterface {
    public function save(Company $company): void {
        // Сохранение через Eloquent
    }
}
```

#### **Services/** - Внешние сервисы
**Что это:** Интеграция с внешними API и сервисами.

**Ваши примеры:**
- `EgrulApiService` - работа с API ЕГРЮЛ
- `GigaChatService` - интеграция с ИИ
- `BlockchainService` - работа с блокчейном

---

### 📁 Shared/ - Общие компоненты

**Что это:** Компоненты, используемые несколькими доменами.

**Ваши примеры:**
- `Inn` - используется и в Company, и в User
- `Money` - используется в Deal, Ad
- `Email` - используется везде

---

## 🔄 Как слои взаимодействуют?

```
HTTP Request → Controller → Application Service → Domain Service → Entity
                     ↓
            Infrastructure ← Domain Repository ← Domain Entity
```

### Пример потока для создания сделки:

1. **Controller** получает HTTP-запрос
2. **Application Service** координирует процесс
3. **Domain Service** применяет бизнес-правила
4. **Entity** изменяет свое состояние
5. **Repository** сохраняет изменения
6. **Event** уведомляет другие части системы

---

## 🎯 Что нужно исправить в вашем коде

### 1. **Модели → Entities**

**Текущие проблемы:**
- Ваши модели (`Company.php`, `Deal.php`) смешивают Eloquent и бизнес-логику
- Нет валидации на уровне домена

**Что делать:**
```php
// Было (в Models/Company.php):
class Company extends Model {
    public function needsAiUpdate(): bool { ... }
}

// Станет (в Domain/Company/Entities/Company.php):  
class Company {
    private Inn $inn;
    private CompanyName $name;
    
    public function needsAiUpdate(): bool { ... }
}
```

### 2. **Сервисы → разделить на слои**

**Текущие проблемы:**
- `AdService`, `DealService` делают слишком много
- Смешивают координацию и бизнес-логику

**Что делать:**
```php
// Было:
class AdService {
    public function create() { ... } // 50 строк кода
}

// Станет:
// Application/Ad/Services/AdApplicationService.php
class AdApplicationService {
    public function createAd(CreateAdCommand $command) {
        // Только координация
    }
}

// Domain/Ad/Services/AdValidationService.php  
class AdValidationService {
    public function validateAd(Ad $ad) {
        // Только валидация
    }
}
```

### 3. **Добавить Value Objects**

**Что добавить:**
- `Inn` для валидации ИНН
- `Money` для работы с ценами
- `AdTitle`, `AdDescription` с валидацией

### 4. **Создать Repository интерфейсы**

**Что сделать:**
```php
// Domain/Company/Contracts/CompanyRepositoryInterface.php
interface CompanyRepositoryInterface {
    public function save(Company $company): void;
    public function findById(CompanyId $id): ?Company;
    public function findByInn(Inn $inn): ?Company;
}
```

### 5. **Вынести DTO в Commands/Queries**

**Ваши DTO уже хороши, но нужно их переместить:**
```
// Было: DTO/Company/CompanyData.php
// Станет: Application/Company/Queries/GetCompanyQuery.php
```

---

## 📋 План миграции вашего кода

### Этап 1 (неделя 1): Value Objects
- Создать `Inn`, `Money`, `Email` 
- Обновить модели для использования VO

### Этап 2 (неделя 2): Entities  
- Перенести бизнес-логику из моделей в Entity
- Оставить модели только для Eloquent

### Этап 3 (неделя 3): Repositories
- Создать интерфейсы репозиториев
- Реализовать через Eloquent

### Этап 4 (неделя 4): Application Services
- Разделить текущие сервисы на слои
- Создать Commands/Queries

### Этап 5 (неделя 5): Events
- Перенести события в доменные
- Настроить обработчиков

---

## ✅ Преимущества после миграции

1. **Читаемость** - каждый класс имеет одну ответственность
2. **Тестируемость** - легко тестировать изолированные части  
3. **Расширяемость** - легко добавлять новые домены
4. **Надежность** - Value Objects защищают от некорректных данных
5. **Поддерживаемость** - новый разработчик быстро разберется

---

Хотите начать с какого-то конкретного домена? Рекомендую начать с **Company** - у него уже есть хорошая бизнес-логика.
