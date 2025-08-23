# 👤 План DDD миграции для User модели

## 📊 Анализ текущей структуры User

### Текущие файлы:
- `app/Models/User.php` - Eloquent модель (103 строки)
- `app/DTO/User/UserData.php` - Spatie DTO  
- `app/DTO/UserSetting/UserProfileData.php` - DTO профиля
- `app/Services/User/UserProfileService.php` - Сервис профиля
- `app/Shared/ValueObjects/Ids/UserId.php` - ✅ Уже создан

### Текущая бизнес-логика в User:
- Проверка ролей: `isDirector()`, `isManager()`
- Связи: `company()`, `ads()`, `dealsAsBuyer()`, `dealsAsSeller()`
- Медиа: аватары пользователей
- Аутентификация: extends Authenticatable

---

## 🎯 Предлагаемая DDD структура

### Domain слой (`app/Domain/User/`):

#### Value Objects:
- `UserId` - ✅ уже есть в `app/Shared/ValueObjects/Ids/`
- `UserEmail` - валидация email адресов
- `UserName` - валидация имени пользователя  
- `UserRole` - enum с ролями (director, manager, user)
- `UserPassword` - хеширование и валидация паролей

#### Entities:
- `User` - главная доменная сущность

#### Contracts:
- `UserRepositoryInterface` - интерфейс репозитория

#### Events:
- `UserWasRegistered`
- `UserRoleWasChanged` 
- `UserProfileWasUpdated`

#### Exceptions:
- `InvalidUserRoleException`
- `UserAlreadyExistsException`

### Infrastructure слой (`app/Infrastructure/User/`):

#### Models:
- `UserModel` - технический Eloquent класс

#### Repositories:
- `EloquentUserRepository` - реализация репозитория

### Application слой (`app/Application/User/`):

#### DTOs:
- `RegisterUserCommand`
- `UpdateUserProfileCommand`
- `ChangeUserRoleCommand`

#### UseCases:
- `RegisterUserUseCase`
- `UpdateUserProfileUseCase`
- `ChangeUserRoleUseCase`
- `GetUserProfileUseCase`

---

## 🚀 Особенности миграции User

### Сложности:
1. **Аутентификация Laravel** - User extends Authenticatable
2. **Медиа-файлы** - аватары пользователей
3. **Множественные связи** - ads, deals, company
4. **Роли пользователей** - бизнес-логика ролей

### Решения:
1. **Infrastructure Model наследует Authenticatable**
2. **Медиа остается в Infrastructure слое**  
3. **Связи загружаются через отдельные Repository**
4. **Роли переносим в Domain как Value Object**

---

## 📋 План выполнения (сжатый)

### 1. Domain слой:
- [ ] `UserEmail::fromString()` + валидация
- [ ] `UserName::fromString()` + валидация  
- [ ] `UserRole` enum (DIRECTOR, MANAGER, USER)
- [ ] `UserPassword::fromPlain()` + хеширование
- [ ] `User` Entity с методами `register()`, `changeRole()`, `updateProfile()`
- [ ] `UserRepositoryInterface`

### 2. Infrastructure слой:
- [ ] `UserModel extends Authenticatable` 
- [ ] `EloquentUserRepository` с методами `findByEmail()`, `findByRole()`

### 3. Application слой:
- [ ] `RegisterUserCommand::fromRequest()`
- [ ] `RegisterUserUseCase::execute()`
- [ ] `UpdateUserProfileUseCase::execute()`
- [ ] `UserServiceProvider`

### 4. UI слой:
- [ ] Рефакторинг `RegisteredUserController`
- [ ] Обновление User-related Controllers
- [ ] Обновление Request classes

### 5. Тестирование:
- [ ] `UserTest.php` - доменная логика
- [ ] `UserRoleTest.php` - роли и права
- [ ] `RegisterUserUseCaseTest.php` - Use Cases
- [ ] `UserControllerTest.php` - интеграционные тесты

---

## ⚠️ Критические моменты:

1. **Не ломать аутентификацию** - Infrastructure Model должна продолжать работать с Auth
2. **Сохранить API** - фронтенд не должен заметить изменений
3. **Постепенная миграция** - сначала новые Use Cases, потом замена старых
4. **Тестирование** - каждый шаг должен быть протестирован

---

Следующий шаг: создать базовые Value Objects для User (UserEmail, UserRole) и начать с простых Use Cases.
