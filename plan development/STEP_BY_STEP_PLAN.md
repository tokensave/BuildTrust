# 🚀 Пошаговый план улучшения проекта Whiteboard

## Sprint 1: Инфраструктура и основы качества (Недели 1-2)

### ✅ Шаг 1.1: Улучшение Docker окружения

**Что делаем:** Настраиваем полноценную среду разработки с всеми нужными сервисами.

**Действия:**
1. Создать папку `docker/centrifugo/`
2. Создать файл `docker/centrifugo/config.json` с конфигурацией Centrifugo:
```json
{
  "token_hmac_secret_key": "your-secret-key-change-in-production",
  "admin_password": "admin",
  "admin_secret": "admin-secret-change-in-production", 
  "api_key": "your-api-key-change-in-production",
  "allowed_origins": ["http://localhost", "http://localhost:80"],
  "namespaces": [
    {
      "name": "notifications",
      "anonymous": false,
      "subscribe_to_publish": true,
      "join_leave": true,
      "presence": true,
      "history_size": 10,
      "history_ttl": "300s"
    },
    {
      "name": "chat", 
      "anonymous": false,
      "subscribe_to_publish": true,
      "join_leave": true,
      "presence": true,
      "history_size": 100,
      "history_ttl": "24h"
    }
  ]
}
```

3. Заменить ваш `docker-compose.yml` на улучшенную версию (файл `docker-compose.improved.yml`)

4. Обновить `.env` файл:
```env
# Добавить переменные для Centrifugo
CENTRIFUGO_SECRET=your-secret-key-change-in-production
CENTRIFUGO_ADMIN_PASSWORD=admin
CENTRIFUGO_ADMIN_SECRET=admin-secret-change-in-production
CENTRIFUGO_API_KEY=your-api-key-change-in-production

# Mailhog для разработки
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

**Зачем:** Получаем удобную среду разработки где все сервисы работают вместе и не мешают системе.

### ✅ Шаг 1.2: Добавление инструментов качества кода

**Что делаем:** Добавляем инструменты для автоматической проверки кода.

**Действия:**
1. Установить пакеты для анализа кода:
```bash
composer require --dev larastan/larastan phpstan/phpstan
```

2. Создать файл `phpstan.neon` в корне проекта:
```neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
    level: 5
    ignoreErrors:
        - '#PHPDoc tag @var#'
        - '#Call to an undefined method Illuminate\\Database\\Eloquent\\Builder#'
    
    checkModelProperties: true
    checkFunctionNameCase: true
```

3. Добавить в `composer.json` скрипты:
```json
"scripts": {
    "analyse": "phpstan analyse",
    "format": "./vendor/bin/pint",
    "format-test": "./vendor/bin/pint --test",
    "test": "php artisan test"
}
```

**Зачем:** Автоматически находим ошибки в коде до того, как они попадут в продакшн.

### ✅ Шаг 1.3: Настройка CI/CD

**Что делаем:** Создаем автоматические проверки кода при каждом коммите.

**Действия:**
1. Создать папку `.github/workflows/`
2. Создать файл `.github/workflows/ci.yml`:
```yaml
name: CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v4
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv
        coverage: none

    - name: Cache Composer packages
      id: composer-cache
      uses: actions/cache@v3
      with:
        path: vendor
        key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
        restore-keys: |
          ${{ runner.os }}-php-

    - name: Install dependencies
      run: composer install --prefer-dist --no-interaction

    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"

    - name: Generate key
      run: php artisan key:generate

    - name: Directory Permissions
      run: chmod -R 777 storage bootstrap/cache

    - name: Run Pint (Code Style)
      run: ./vendor/bin/pint --test

    - name: Run PHPStan (Static Analysis)
      run: ./vendor/bin/phpstan analyse

    - name: Create Database
      run: |
        mkdir -p database
        touch database/database.sqlite

    - name: Execute tests
      env:
        DB_CONNECTION: sqlite
        DB_DATABASE: database/database.sqlite
      run: php artisan test
```

**Зачем:** Автоматически проверяем, что новый код не ломает проект и соответствует стандартам качества.

---

## Sprint 2: Архитектурные улучшения (Недели 3-4)

### ✅ Шаг 2.1: Создание структуры папок

**Что делаем:** Создаем правильную архитектуру для растущего проекта.

**Действия:**
1. Создать папки:
```
app/
├── Actions/          # Команды для выполнения бизнес-логики
├── DTO/              # Объекты для передачи данных
├── ValueObjects/     # Неизменяемые объекты (деньги, ИНН, etc)
├── Contracts/        # Интерфейсы
│   ├── Services/
│   └── Repositories/
├── Repositories/     # Работа с данными
├── Exceptions/       # Доменные исключения
└── Enums/           # Перенести существующие Enums сюда
```

**Зачем:** Четкая структура помогает понимать, где что лежит, и упрощает разработку.

### ✅ Шаг 2.2: Создание базовых Value Objects

**Что делаем:** Создаем объекты для работы с деньгами, ИНН и другими важными данными.

**Действия:**
1. Создать `app/ValueObjects/Inn.php`:
```php
<?php

namespace App\ValueObjects;

use App\Exceptions\InvalidInnException;

class Inn
{
    private readonly string $value;

    public function __construct(string $inn)
    {
        if (!$this->isValid($inn)) {
            throw new InvalidInnException("Invalid INN: {$inn}");
        }
        $this->value = $inn;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isIndividual(): bool
    {
        return strlen($this->value) === 12;
    }

    public function isLegalEntity(): bool
    {
        return strlen($this->value) === 10;
    }

    private function isValid(string $inn): bool
    {
        // Убираем пробелы и проверяем длину
        $inn = preg_replace('/\s+/', '', $inn);
        
        if (!preg_match('/^\d{10}$/', $inn) && !preg_match('/^\d{12}$/', $inn)) {
            return false;
        }

        // Здесь можно добавить более сложную валидацию контрольных сумм
        return true;
    }
}
```

3. Создать `app/Exceptions/InvalidInnException.php`:
```php
<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidInnException extends InvalidArgumentException
{
    //
}
```

**Зачем:** Value Objects защищают от некорректных данных и делают код более читаемым. 

**Примечание:** Класс Money пока не создаем - финансовые операции будет обрабатывать отдельный микросервис.

### ✅ Шаг 2.3: Улучшение моделей

**Что делаем:** Добавляем бизнес-логику в модели и улучшаем их.

**Действия:**
1. Обновить `app/Models/Company.php`:
```php
<?php

namespace App\Models;

use App\ValueObjects\Inn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'inn',
        'name', 
        'email',
        'phone',
        'city',
        'address',
        'website',
        'verified',
        'verification_score',
        'verification_status', 
        'verification_data',
        'risk_level',
        'last_verified_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verification_score' => 'integer',
        'verification_data' => 'array',
        'last_verified_at' => 'datetime',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function ads(): HasMany
    {
        return $this->hasManyThrough(Ad::class, User::class);
    }

    // Business Logic
    public function getInn(): Inn
    {
        return new Inn($this->inn);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function needsReverification(): bool
    {
        if (!$this->last_verified_at) {
            return true;
        }

        // Перепроверяем каждые 30 дней
        return $this->last_verified_at->diffInDays(now()) > 30;
    }

    public function getRiskLevelColor(): string
    {
        return match ($this->risk_level) {
            'low' => 'green',
            'medium' => 'yellow', 
            'high' => 'red',
            default => 'gray',
        };
    }
}
```

2. Обновить `app/Models/Deal.php`:
```php
<?php

namespace App\Models;

use App\Enums\DealEnums\DealStatusEnum;
use App\Events\DealCreatedOrUpdatedEvent;
use App\Exceptions\InvalidDealTransitionException;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Deal extends Model implements HasMedia
{
    use HasFactory, HasUuid, InteractsWithMedia;

    protected $fillable = [
        'ad_id',
        'buyer_id', 
        'seller_id',
        'price',
        'status',
        'notes',
        'signed_at',
        'on_chain_id',
    ];

    protected $casts = [
        'status' => DealStatusEnum::class,
        'price' => 'decimal:2',
        'signed_at' => 'datetime',
    ];

    // Relationships
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Business Logic
    public function getFormattedPrice(): string
    {
        return number_format($this->price, 2, '.', ' ') . ' ₽';
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === DealStatusEnum::PENDING;
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [
            DealStatusEnum::COMPLETED,
            DealStatusEnum::CANCELLED
        ]);
    }

    public function confirm(): void
    {
        if (!$this->canBeConfirmed()) {
            throw new InvalidDealTransitionException(
                "Cannot confirm deal in status: {$this->status->value}"
            );
        }

        $this->update([
            'status' => DealStatusEnum::CONFIRMED,
            'signed_at' => now(),
        ]);

        event(new DealCreatedOrUpdatedEvent($this));
    }

    public function cancel(string $reason = null): void
    {
        if (!$this->canBeCancelled()) {
            throw new InvalidDealTransitionException(
                "Cannot cancel deal in status: {$this->status->value}"
            );
        }

        $this->update([
            'status' => DealStatusEnum::CANCELLED,
            'notes' => $this->notes . "\n\nCancellation reason: " . $reason,
        ]);

        event(new DealCreatedOrUpdatedEvent($this));
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk('public')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'image/jpeg', 'image/png']);
    }
}
```

3. Создать `app/Exceptions/InvalidDealTransitionException.php`:
```php
<?php

namespace App\Exceptions;

use Exception;

class InvalidDealTransitionException extends Exception
{
    //
}
```

**Зачем:** Модели становятся умнее и защищают от некорректных операций.

---

## Sprint 3: Расширение базы данных (Недели 5-6)

### ✅ Шаг 3.1: Создание миграций для расширенной функциональности

**Что делаем:** Добавляем новые поля и таблицы для продвинутой функциональности.

**Действия:**
1. Создать миграцию для расширения таблицы companies:
```bash
php artisan make:migration extend_companies_table_for_verification --table=companies
```

В миграции:
```php
public function up()
{
    Schema::table('companies', function (Blueprint $table) {
        // AI Verification fields
        $table->integer('verification_score')->default(0);
        $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending');
        $table->json('verification_data')->nullable();
        $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
        $table->timestamp('last_verified_at')->nullable();
        
        // Additional info
        $table->text('description')->nullable();
        $table->year('founded_year')->nullable();
        $table->integer('employee_count')->nullable();
        $table->decimal('annual_revenue', 15, 2)->nullable();
        
        // Indexes
        $table->index('verification_status');
        $table->index('risk_level');
    });
}
```

2. Создать таблицу категорий:
```bash
php artisan make:migration create_categories_table
```

3. Создать таблицу атрибутов товаров:
```bash
php artisan make:migration create_attributes_table
```

4. Создать таблицу для связи категорий и атрибутов:
```bash
php artisan make:migration create_category_attributes_table
```

5. Создать таблицу для значений атрибутов объявлений:
```bash
php artisan make:migration create_ad_attribute_values_table
```

**Зачем:** Готовим базу данных для сложной бизнес-логики и ИИ-интеграции.

### ✅ Шаг 3.2: Создание моделей

**Действия:**
1. Создать модели:
```bash
php artisan make:model Category
php artisan make:model Attribute  
php artisan make:model CategoryAttribute
php artisan make:model AdAttributeValue
```

2. Настроить отношения в моделях

**Зачем:** Получаем типизированную работу с новыми сущностями.

---

## Sprint 4: Система ролей и разрешений (Недели 7-8)

### ✅ Шаг 4.1: Установка Laravel Permission

**Действия:**
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan make:seeder RolesAndPermissionsSeeder
```

### ✅ Шаг 4.2: Настройка ролей

**Действия:**
1. Обновить модель User для работы с ролями
2. Создать сидер с ролями и разрешениями
3. Создать middleware для проверки разрешений

**Зачем:** Контролируем, кто что может делать в системе.

---

## 📋 Контрольный список (Checklist) на каждый Sprint

### Перед началом спринта:
- [ ] Убедиться, что предыдущий спринт завершен
- [ ] Сделать backup базы данных
- [ ] Создать новую ветку в git
- [ ] Прочитать план спринта полностью

### После завершения спринта:
- [ ] Запустить тесты: `composer test`
- [ ] Проверить код: `composer analyse`  
- [ ] Отформатировать код: `composer format`
- [ ] Создать PR и проверить CI
- [ ] Обновить документацию если нужно

### Важные команды:
```bash
# Запуск окружения
./vendor/bin/sail up -d

# Проверка качества кода
composer analyse
composer format-test

# Запуск тестов
composer test

# Миграции
php artisan migrate
php artisan migrate:fresh --seed
```

---

## 🎯 Результат каждого Sprint

**Sprint 1:** Настроенная среда разработки с автоматическими проверками
**Sprint 2:** Чистая архитектура с Value Objects и улучшенными моделями  
**Sprint 3:** Расширенная база данных, готовая для сложной логики
**Sprint 4:** Система ролей и разрешений

После 4-го спринта у вас будет:
- ✅ Надежная среда разработки
- ✅ Качественная архитектура кода
- ✅ Готовность к интеграции с ИИ
- ✅ Система безопасности и ролей

---

## ❓ Что делать, если что-то пошло не так

1. **Ошибки в миграциях:** `php artisan migrate:rollback`
2. **Проблемы с Docker:** `docker-compose down -v && docker-compose up -d`
3. **Ошибки композера:** `composer install --no-cache`
4. **Проблемы с правами:** `chmod -R 755 storage bootstrap/cache`

Каждый шаг расписан так, чтобы вы могли выполнять их по порядку. Если есть вопросы по любому шагу - спрашивайте!
