# 🏗️ Пошаговая инструкция по миграции моделей на DDD архитектуру

Этот документ содержит проверенную последовательность шагов для переноса любой модели (User, Ad, Chat, etc.) на Domain Driven Design архитектуру.

## 📋 Общий план миграции

### Этап 1: Подготовка и анализ
1. **Анализ текущей структуры**
2. **Создание структуры папок**  
3. **Выявление Value Objects**

### Этап 2: Domain слой
4. **Создание Value Objects**
5. **Создание Domain Entity**
6. **Создание Repository Interface**
7. **Создание доменных событий**

### Этап 3: Infrastructure слой  
8. **Создание Infrastructure Model**
9. **Создание Repository Implementation**

### Этап 4: Application слой
10. **Создание Command DTOs**
11. **Создание Use Cases**
12. **Создание Service Provider**

### Этап 5: UI слой
13. **Рефакторинг Controllers**
14. **Обновление Request classes**

### Этап 6: Тестирование
15. **Unit тесты Domain слоя**
16. **Unit тесты Application слоя**  
17. **Feature тесты Controller'ов**

### Этап 7: Cleanup
18. **Удаление устаревшего кода**
19. **Проверка зависимостей**

---

## 🔧 Детальная инструкция по этапам

### Этап 1: Подготовка и анализ

#### 1.1 Анализ текущей структуры
```bash
# Найти все файлы связанные с моделью
find app/ -name "*User*" -type f
grep -r "class User" app/
```

#### 1.2 Создание структуры папок
```bash
mkdir -p app/Domain/{ModelName}/{Entities,ValueObjects,Contracts,Events,Exceptions}
mkdir -p app/Infrastructure/{ModelName}/{Models,Repositories} 
mkdir -p app/Application/{ModelName}/{UseCases,DTOs}
mkdir -p tests/Unit/Domain/{ModelName}
mkdir -p tests/Unit/Application/{ModelName}
mkdir -p tests/Feature/{ModelName}
```

#### 1.3 Выявление Value Objects
- ID объекты: `{ModelName}Id`
- Статусы: `{ModelName}Status` 
- Специфичные типы: `Email`, `Phone`, `UserName`, etc.

---

### Этап 2: Domain слой

#### 2.1 Создание Value Objects
Каждый Value Object должен содержать:
- `private readonly` конструктор
- `static fromString()` / `fromInt()` методы
- `toString()` / `toInt()` методы  
- `equals()` метод
- Валидацию в конструкторе

**Пример структуры:**
```php
// app/Domain/{Model}/ValueObjects/{ModelName}Id.php
final readonly class {ModelName}Id
{
    public static function fromInt(int $id): self
    public function toInt(): int
    public function equals({ModelName}Id $other): bool
}
```

#### 2.2 Создание Domain Entity
Главная доменная сущность должна содержать:
- `static create()` - для новых объектов
- `static reconstruct()` - для восстановления из БД
- Бизнес-методы: `activate()`, `deactivate()`, etc.
- Доменные события: `recordThat()`, `getRecordedEvents()`
- Геттеры для всех полей
- Валидацию бизнес-правил

#### 2.3 Создание Repository Interface
```php
// app/Domain/{Model}/Contracts/{ModelName}RepositoryInterface.php
interface {ModelName}RepositoryInterface
{
    public function save({ModelName} $model): void;
    public function findById({ModelName}Id $id): ?{ModelName};
    public function delete({ModelName}Id $id): void;
    public function nextId(): {ModelName}Id;
    // Специфичные методы: findByEmail(), findActive(), etc.
}
```

---

### Этап 3: Infrastructure слой

#### 3.1 Создание Infrastructure Model
```php
// app/Infrastructure/{Model}/Models/{ModelName}Model.php
final class {ModelName}Model extends Model
{
    protected $table = '{table_name}';
    protected $fillable = [...];
    protected $casts = [...];
    // Только технические методы, НЕТ бизнес-логики!
}
```

#### 3.2 Создание Repository Implementation
```php
// app/Infrastructure/{Model}/Repositories/Eloquent{ModelName}Repository.php
final class Eloquent{ModelName}Repository implements {ModelName}RepositoryInterface
{
    public function save({ModelName} $model): void
    public function findById({ModelName}Id $id): ?{ModelName}
    private function toDomain({ModelName}Model $model): {ModelName}
    private function toDomainCollection($models): array
}
```

---

### Этап 4: Application слой

#### 4.1 Создание Command DTOs
```php
// app/Application/{Model}/DTOs/Create{ModelName}Command.php
final readonly class Create{ModelName}Command
{
    public static function fromArray(array $data): self
    public static function fromRequest({ModelName}Request $request): self
}
```

#### 4.2 Создание Use Cases
```php
// app/Application/{Model}/UseCases/Create{ModelName}UseCase.php
final class Create{ModelName}UseCase
{
    public function execute(Create{ModelName}Command $command): {ModelName}
    private function validateCommand(): void
}
```

#### 4.3 Создание Service Provider
```php
// app/Providers/{ModelName}ServiceProvider.php
class {ModelName}ServiceProvider extends ServiceProvider
{
    public function register(): void // Биндинги интерфейсов
    public function boot(): void     // Event listeners
}
```

---

### Этап 5: UI слой

#### 5.1 Рефакторинг Controllers
- Заменить сервисы на Use Cases
- Использовать Command DTOs  
- Добавить обработку доменных исключений
- Сохранить формат данных для фронтенда

#### 5.2 Обновление Request classes
- Заменить enum'ы на доменные Value Objects
- Обновить validation rules

---

### Этап 6: Тестирование

#### 6.1 Unit тесты Domain слоя
```php
tests/Unit/Domain/{Model}/{ModelName}Test.php
tests/Unit/Domain/{Model}/{ModelName}StatusTest.php
tests/Unit/Domain/{Model}/{ValueObject}Test.php
```

#### 6.2 Unit тесты Application слоя
```php
tests/Unit/Application/{Model}/Create{ModelName}UseCaseTest.php
tests/Unit/Application/{Model}/Update{ModelName}UseCaseTest.php
```

#### 6.3 Feature тесты Controller'ов
```php
tests/Feature/{Model}/{ModelName}ControllerTest.php
```

---

### Этап 7: Cleanup

#### 7.1 Удаление устаревшего кода
```bash
# Найти неиспользуемые файлы
grep -r "OldClassName" app/
# Удалить после проверки
rm app/Services/{Model}/{OldService}.php
```

#### 7.2 Проверка зависимостей
```bash
# Запустить тесты
php artisan test
# Проверить автозагрузку
composer dump-autoload
```

---

## ✅ Чек-лист проверки после миграции

### Domain слой:
- [ ] Value Objects имеют валидацию
- [ ] Entity содержит всю бизнес-логику
- [ ] Repository Interface определен
- [ ] Доменные события созданы
- [ ] Unit тесты покрывают бизнес-логику

### Application слой:
- [ ] Use Cases выполняют одну задачу
- [ ] Command DTOs инкапсулируют данные
- [ ] Service Provider настроен
- [ ] Unit тесты покрывают Use Cases

### Infrastructure слой:
- [ ] Models содержат только технические методы
- [ ] Repository Implementation работает с БД
- [ ] Преобразование Domain ↔ Infrastructure

### UI слой:
- [ ] Controllers тонкие (только координация)
- [ ] Request classes обновлены
- [ ] Feature тесты проходят
- [ ] Фронтенд получает данные в том же формате

### Общее:
- [ ] Все тесты проходят
- [ ] Нет циклических зависимостей
- [ ] Service Provider зарегистрирован
- [ ] Автозагрузка работает

---

## 🎯 Команды для проверки в Tinker

### Проверка Domain слоя:
```php
// Создание Entity
$entity = {ModelName}::create($id, $params...);

// Проверка бизнес-методов
$entity->activate();
$entity->getStatus()->label();

// Проверка событий
$entity->getRecordedEvents();
```

### Проверка Application слоя:
```php
// Создание Use Case через DI
$useCase = app(Create{ModelName}UseCase::class);

// Создание команды
$command = Create{ModelName}Command::fromArray($data);

// Выполнение Use Case
$result = $useCase->execute($command);
```

### Проверка Infrastructure слоя:
```php
// Создание Repository через DI
$repository = app({ModelName}RepositoryInterface::class);

// Тест сохранения
$repository->save($entity);

// Тест загрузки
$loaded = $repository->findById($id);
```

---

## 📚 Полезные команды

### Создание тестов:
```bash
php artisan make:test Unit/Domain/{Model}/{ModelName}Test --unit
php artisan make:test Feature/{Model}/{ModelName}ControllerTest
```

### Запуск тестов:
```bash
php artisan test --filter={ModelName}
php artisan test tests/Unit/Domain/{Model}/
php artisan test tests/Feature/{Model}/
```

### Проверка автозагрузки:
```bash
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

---

Следуя этой инструкции, вы сможете мигрировать любую модель на чистую DDD архитектуру с полным покрытием тестами и сохранением обратной совместимости.
