# 🏛️ Domain Layer - Слой домена

## 📝 Назначение
Это **сердце приложения** - здесь живет вся бизнес-логика. Domain слой не знает о базах данных, HTTP, фреймворках - только о бизнесе.

## 📁 Структура папок

### **Entities/** - Доменные сущности
**Что здесь:** Основные бизнес-объекты с уникальной идентичностью.

**Принципы:**
- ✅ Содержат бизнес-логику и правила
- ✅ Защищают свою целостность  
- ✅ Не зависят от внешних сервисов
- ❌ НЕ содержат логику сохранения данных

**Пример:**
```php
// Domain/Company/Entities/Company.php
class Company {
    private CompanyId $id;
    private Inn $inn;
    private CompanyName $name;
    private VerificationStatus $status;
    
    public function verify(): void {
        if (!$this->canBeVerified()) {
            throw new CompanyCannotBeVerifiedException();
        }
        
        $this->status = VerificationStatus::verified();
        $this->recordThat(new CompanyWasVerified($this->id));
    }
    
    private function canBeVerified(): bool {
        return $this->inn->isValid() && $this->name->isNotEmpty();
    }
}
```

### **ValueObjects/** - Объекты-значения
**Что здесь:** Неизменяемые объекты, которые описывают характеристики Entity.

**Принципы:**
- ✅ Неизменяемые (immutable)
- ✅ Сравниваются по значению, а не по ссылке
- ✅ Содержат валидацию
- ✅ Не имеют идентичности

**Примеры:**
```php
// Domain/Company/ValueObjects/Inn.php
final class Inn {
    private readonly string $value;
    
    public function __construct(string $inn) {
        if (!$this->isValid($inn)) {
            throw new InvalidInnException($inn);
        }
        $this->value = $inn;
    }
    
    public function isLegalEntity(): bool {
        return strlen($this->value) === 10;
    }
}
```

### **Services/** - Доменные сервисы
**Что здесь:** Бизнес-логика, которая не принадлежит конкретной Entity.

**Когда использовать:**
- Логика затрагивает несколько Entity
- Сложные вычисления или алгоритмы
- Бизнес-правила, которые не "помещаются" в Entity

**Пример:**
```php
// Domain/Company/Services/CompanyVerificationService.php
class CompanyVerificationService {
    public function calculateReliabilityScore(
        Company $company, 
        array $financialData
    ): ReliabilityScore {
        // Сложная логика расчета надежности
        $score = 0;
        
        if ($company->hasValidInn()) {
            $score += 30;
        }
        
        if ($this->hasGoodFinancialHistory($financialData)) {
            $score += 40;
        }
        
        return ReliabilityScore::fromInt($score);
    }
}
```

### **Events/** - Доменные события
**Что здесь:** События, которые происходят в домене.

**Принципы:**
- ✅ Описывают что произошло (прошедшее время)
- ✅ Неизменяемые
- ✅ Содержат минимум данных для обработчиков

**Пример:**
```php
// Domain/Company/Events/CompanyWasVerified.php
final class CompanyWasVerified {
    public function __construct(
        public readonly CompanyId $companyId,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
```

### **Contracts/** - Интерфейсы
**Что здесь:** Контракты для репозиториев и внешних сервисов.

**Принципы:**
- ✅ Определяют, что нужно домену от внешнего мира
- ✅ Реализуются в Infrastructure слое
- ✅ Позволяют тестировать Domain изолированно

**Пример:**
```php
// Domain/Company/Contracts/CompanyRepositoryInterface.php
interface CompanyRepositoryInterface {
    public function save(Company $company): void;
    public function findById(CompanyId $id): ?Company;
    public function findByInn(Inn $inn): ?Company;
    public function nextId(): CompanyId;
}
```

### **Exceptions/** - Доменные исключения
**Что здесь:** Исключения, специфичные для этого домена.

**Принципы:**
- ✅ Наследуются от базовых исключений
- ✅ Содержат понятные бизнес-сообщения
- ✅ Помогают обработать ошибки на уровне приложения

**Пример:**
```php
// Domain/Company/Exceptions/CompanyAlreadyVerifiedException.php
final class CompanyAlreadyVerifiedException extends DomainException {
    public static function forCompany(CompanyId $id): self {
        return new self("Компания {$id->toString()} уже верифицирована");
    }
}
```

## 🔧 Правила Domain слоя

### ✅ Что МОЖНО делать:
- Содержать бизнес-логику
- Создавать и выбрасывать доменные события
- Использовать Value Objects
- Определять интерфейсы для внешних зависимостей
- Валидировать данные на уровне бизнес-правил

### ❌ Что НЕЛЬЗЯ делать:
- Зависеть от фреймворков (Laravel, Eloquent)
- Работать с базой данных напрямую
- Делать HTTP запросы
- Использовать глобальные функции (config(), auth())
- Зависеть от Infrastructure или Application слоев

## 📊 Примеры из вашего проекта

### До DDD:
```php
// Models/Company.php
class Company extends Model {
    public function needsAiUpdate(): bool {
        if ($this->ai_status === 'failed') {
            return true;
        }
        return $this->ai_last_check?->lt(now()->subDay()) ?? true;
    }
    
    // Смешана бизнес-логика с Eloquent
}
```

### После DDD:
```php
// Domain/Company/Entities/Company.php
class Company {
    public function needsAiUpdate(): bool {
        if ($this->aiAnalysis->hasFailed()) {
            return true;
        }
        
        return $this->aiAnalysis->isOutdated(Period::days(1));
    }
    
    // Чистая бизнес-логика без фреймворка
}
```

---

## 🎯 С чего начать миграцию

1. **Начните с Value Objects** - создайте `Inn`, `Money`, `Email`
2. **Извлеките Entity** - перенесите бизнес-методы из моделей  
3. **Создайте интерфейсы** - определите контракты репозиториев
4. **Добавьте события** - замените прямые вызовы событиями домена

Помните: Domain слой должен быть **независимым** и **тестируемым** без поднятия всего приложения!
