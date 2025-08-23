# 🔧 Infrastructure Layer - Инфраструктурный слой

## 📝 Назначение
**Техническая реализация** - здесь живут детали работы с базами данных, внешними API, файловой системой. Это "грязный" слой, который знает о технических деталях.

## 📁 Структура папок

### **Repositories/** - Реализации репозиториев
**Что здесь:** Конкретные реализации интерфейсов репозиториев из Domain слоя.

**Принципы:**
- ✅ Реализуют интерфейсы из Domain/Contracts/
- ✅ Содержат только логику работы с данными
- ✅ Преобразуют данные между Domain и Infrastructure
- ❌ НЕ содержат бизнес-логику
- ❌ НЕ знают о HTTP или других слоях

**Пример:**
```php
// Infrastructure/Company/Repositories/EloquentCompanyRepository.php
class EloquentCompanyRepository implements CompanyRepositoryInterface {
    public function save(Company $company): void {
        $model = CompanyModel::find($company->getId()->toInt()) 
                    ?? new CompanyModel();
        
        $model->fill([
            'inn' => $company->getInn()->toString(),
            'name' => $company->getName()->toString(),
            'email' => $company->getEmail()->toString(),
            'verification_status' => $company->getStatus()->toString(),
        ]);
        
        $model->save();
    }
    
    public function findByInn(Inn $inn): ?Company {
        $model = CompanyModel::where('inn', $inn->toString())->first();
        
        if (!$model) {
            return null;
        }
        
        // Преобразуем модель в доменную сущность
        return $this->toDomain($model);
    }
    
    private function toDomain(CompanyModel $model): Company {
        return Company::reconstruct(
            id: CompanyId::fromInt($model->id),
            inn: Inn::fromString($model->inn),
            name: CompanyName::fromString($model->name),
            email: Email::fromString($model->email),
            status: VerificationStatus::fromString($model->verification_status)
        );
    }
}
```

### **Services/** - Внешние сервисы
**Что здесь:** Интеграция с внешними API и сервисами.

**Принципы:**
- ✅ Работают с внешними системами (API, файлы, очереди)
- ✅ Реализуют интерфейсы из Domain слоя
- ✅ Обрабатывают технические ошибки
- ✅ Преобразуют данные из внешнего формата в доменный

**Примеры:**
```php
// Infrastructure/Company/Services/EgrulApiService.php
class EgrulApiService implements CompanyDataProviderInterface {
    public function getCompanyData(Inn $inn): CompanyExternalData {
        try {
            $response = Http::get('https://api.egrul.ru/search', [
                'inn' => $inn->toString()
            ]);
            
            if (!$response->successful()) {
                throw new ExternalServiceException('EGRUL API недоступен');
            }
            
            $data = $response->json();
            
            return new CompanyExternalData(
                inn: $inn,
                name: $data['name'] ?? null,
                address: $data['address'] ?? null,
                status: $data['status'] ?? null
            );
            
        } catch (Exception $e) {
            throw new ExternalServiceException(
                'Ошибка получения данных из ЕГРЮЛ: ' . $e->getMessage()
            );
        }
    }
}

// Infrastructure/AI/Services/GigaChatApiService.php  
class GigaChatApiService implements CompanyAnalysisServiceInterface {
    public function analyzeCompany(Inn $inn, CompanyName $name): CompanyAnalysis {
        $response = $this->gigaChat->analyzeCompany($inn->toString(), $name->toString());
        
        return new CompanyAnalysis(
            riskLevel: RiskLevel::fromString($response['risk_level']),
            score: VerificationScore::fromInt($response['score']),
            recommendations: $response['recommendations'] ?? []
        );
    }
}
```

## 📊 Примеры из вашего проекта

### Что у вас сейчас:
```php
// Models/Company.php - смешивает Eloquent и бизнес-логику
class Company extends Model {
    public function needsAiUpdate(): bool {
        return $this->ai_last_check?->lt(now()->subDay()) ?? true;
    }
    
    public function users(): HasMany {
        return $this->hasMany(User::class);
    }
}
```

### Что будет после DDD:

#### 1. Domain Entity (чистая бизнес-логика):
```php
// Domain/Company/Entities/Company.php
class Company {
    public function needsAiUpdate(): bool {
        return $this->aiAnalysis->isOutdated(Period::days(1));
    }
    
    public function verify(): void {
        if (!$this->canBeVerified()) {
            throw new CompanyCannotBeVerifiedException();
        }
        
        $this->status = VerificationStatus::verified();
    }
}
```

#### 2. Infrastructure Model (только данные):
```php
// Infrastructure/Company/Models/CompanyModel.php
class CompanyModel extends Model {
    protected $table = 'companies';
    
    protected $fillable = [
        'inn', 'name', 'email', 'verification_status'
    ];
    
    // Только отношения Eloquent, без бизнес-логики
    public function users(): HasMany {
        return $this->hasMany(UserModel::class, 'company_id');
    }
}
```

#### 3. Repository (преобразование данных):
```php
// Infrastructure/Company/Repositories/EloquentCompanyRepository.php
class EloquentCompanyRepository implements CompanyRepositoryInterface {
    public function save(Company $company): void {
        // Сохранение доменной сущности через Eloquent модель
    }
    
    public function findByInn(Inn $inn): ?Company {
        // Поиск и преобразование в доменную сущность
    }
}
```

## 🔄 Mapping между слоями

### Domain → Infrastructure (сохранение):
```php
public function save(Company $company): void {
    $model = CompanyModel::find($company->getId()->toInt()) ?? new CompanyModel();
    
    // Преобразуем доменные объекты в примитивы
    $model->fill([
        'inn' => $company->getInn()->toString(),
        'name' => $company->getName()->toString(),
        'email' => $company->getEmail()->toString(),
        'verification_status' => $company->getStatus()->value,
        'verification_score' => $company->getScore()?->toInt(),
    ]);
    
    $model->save();
}
```

### Infrastructure → Domain (восстановление):
```php
private function toDomain(CompanyModel $model): Company {
    return Company::reconstruct(
        id: CompanyId::fromInt($model->id),
        inn: Inn::fromString($model->inn),
        name: CompanyName::fromString($model->name),
        email: Email::fromString($model->email),
        status: VerificationStatus::from($model->verification_status),
        score: $model->verification_score ? 
               VerificationScore::fromInt($model->verification_score) : null
    );
}
```

## 🎯 Что нужно сделать с вашим кодом

### 1. **Разделить модели**

**Текущие модели:**
- `Models/Company.php` - содержит и данные, и бизнес-логику
- `Models/Deal.php` - то же самое  
- `Models/Ad.php` - и здесь

**Что сделать:**
```php
// 1. Создать чистые Infrastructure модели
Infrastructure/Company/Models/CompanyModel.php (только Eloquent)
Infrastructure/Deal/Models/DealModel.php  
Infrastructure/Ad/Models/AdModel.php

// 2. Создать Domain entities
Domain/Company/Entities/Company.php (только бизнес-логика)
Domain/Deal/Entities/Deal.php
Domain/Ad/Entities/Ad.php

// 3. Создать репозитории
Infrastructure/Company/Repositories/EloquentCompanyRepository.php
```

### 2. **Перенести сервисы**

**Ваш GigaChatService уже хороший!** Нужно только:
```php
// Было:
Services/AI/GigaChatService.php

// Станет:
Infrastructure/AI/Services/GigaChatApiService.php (техническая интеграция)
Domain/AI/Services/CompanyAnalysisService.php (бизнес-логика анализа)
```

### 3. **Создать интерфейсы**

```php
// Domain/Company/Contracts/CompanyRepositoryInterface.php
interface CompanyRepositoryInterface {
    public function save(Company $company): void;
    public function findById(CompanyId $id): ?Company;
    public function findByInn(Inn $inn): ?Company;
}

// Domain/AI/Contracts/CompanyAnalysisServiceInterface.php
interface CompanyAnalysisServiceInterface {
    public function analyzeCompany(Inn $inn, CompanyName $name): CompanyAnalysis;
}
```

### 4. **Настроить DI в ServiceProvider**

```php
// Providers/AppServiceProvider.php
public function register(): void {
    // Привязываем интерфейсы к реализациям
    $this->app->bind(
        CompanyRepositoryInterface::class,
        EloquentCompanyRepository::class
    );
    
    $this->app->bind(
        CompanyAnalysisServiceInterface::class,
        GigaChatApiService::class
    );
}
```

## 🔧 Правила Infrastructure слоя

### ✅ Что МОЖНО делать:
- Работать с базами данных (Eloquent, Query Builder)
- Делать HTTP запросы к внешним API
- Работать с файловой системой
- Использовать Laravel-специфичные компоненты
- Обрабатывать технические исключения

### ❌ Что НЕЛЬЗЯ делать:
- Содержать бизнес-логику
- Знать о правилах домена
- Вызывать методы Domain entities напрямую
- Смешивать разные технические концерны

## 🗂️ Организация файлов

```
Infrastructure/
├── Company/
│   ├── Models/
│   │   └── CompanyModel.php          # Eloquent модель
│   ├── Repositories/
│   │   └── EloquentCompanyRepository.php  # Реализация репозитория
│   └── Services/
│       └── EgrulApiService.php       # Внешний API
├── Deal/
│   ├── Models/
│   │   └── DealModel.php
│   ├── Repositories/
│   │   └── EloquentDealRepository.php
│   └── Services/
│       └── BlockchainService.php     # Ваш существующий сервис
└── AI/
    └── Services/
        └── GigaChatApiService.php    # Переименованный GigaChatService
```

## 🚀 Преимущества разделения

1. **Тестируемость** - можно тестировать домен без БД
2. **Гибкость** - легко заменить Eloquent на что-то другое  
3. **Чистота** - бизнес-логика не зависит от технических деталей
4. **Производительность** - можно оптимизировать запросы отдельно
5. **Расширяемость** - легко добавлять новые источники данных

Infrastructure слой - это **переводчик** между чистым доменом и грязным внешним миром!
