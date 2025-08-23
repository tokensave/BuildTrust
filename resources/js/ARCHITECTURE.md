# Frontend Architecture

Данный проект использует архитектурный паттерн **Feature-Driven Development (FDD)** с ясным разделением на домены и общие компоненты.

## Структура проекта

```
resources/js/
├── components/           # Только UI компоненты (shadcn/ui)
│   └── ui/
├── features/            # Бизнес-логика по доменам
│   ├── ads/
│   ├── auth/
│   ├── chat/
│   ├── companies/
│   ├── deals/
│   └── profile/
├── shared/              # Общие компоненты и утилиты
│   ├── components/
│   ├── composables/
│   ├── types/
│   └── utils/
├── pages/               # Страницы приложения
├── layouts/             # Layout компоненты
└── composables/         # Глобальные composables (legacy)
```

## Features (Домены)

Каждая feature содержит:

### 📁 Структура feature
```
features/{domain}/
├── components/          # Компоненты домена
│   ├── cards/          # Карточки
│   ├── forms/          # Формы
│   ├── modals/         # Модальные окна
│   ├── display/        # Компоненты отображения
│   └── navigation/     # Навигационные компоненты
├── composables/        # Бизнес-логика домена
├── types/              # TypeScript типы домена
└── README.md           # Документация домена
```

### 🎯 Текущие Features

#### **ads** - Объявления
- **Компоненты**: Карточки объявлений, формы создания/редактирования, фильтры, галерея изображений
- **Composables**: `useAds`, `useAdForm`, `useAdFilters`
- **Страницы**: `pages/ads/userAds/`

#### **auth** - Аутентификация
- **Компоненты**: Формы входа, регистрации, сброса пароля
- **Composables**: `useAuth`
- **Страницы**: `pages/auth/`

#### **companies** - Компании
- **Компоненты**: Карточки компаний
- **Composables**: `useCompanies`

#### **profile** - Профили пользователей
- **Компоненты**: Формы профиля, аватары, настройки
- **Composables**: `useProfile`
- **Страницы**: `pages/settings/`

#### **chat** - Чат и сообщения
- **Компоненты**: Модали создания сообщений
- **Страницы**: `pages/chat/`

#### **deals** - Сделки
- **Компоненты**: Модали создания сделок
- **Страницы**: `pages/userDeals/`

## Shared (Общие ресурсы)

### 📁 Структура shared
```
shared/
├── components/
│   ├── branding/       # Логотипы, брендинг
│   ├── forms/          # Общие формы (InputError, ImageUploader)
│   ├── layout/         # Layout компоненты
│   ├── navigation/     # Навигация, хлебные крошки
│   ├── settings/       # Компоненты настроек
│   ├── ui/             # Общие UI компоненты
│   └── user/           # Пользовательские компоненты
├── composables/        # Общие composables
├── types/              # Общие TypeScript типы
└── utils/              # Утилиты
```

## Принципы архитектуры

### 🎯 Разделение ответственности
- **Features** содержат доменную логику
- **Shared** содержит переиспользуемые компоненты
- **Components** содержит только UI компоненты

### 📦 Импорты
```typescript
// ✅ Правильно - из features
import { useAds } from '@/features/ads/composables'
import AdCard from '@/features/ads/components/cards/UserAdCard.vue'

// ✅ Правильно - из shared
import { useNotifications } from '@/shared/composables'
import InputError from '@/shared/components/forms/InputError.vue'

// ✅ Правильно - UI компоненты
import { Button } from '@/components/ui/button'
```

### 🔄 Composables

#### Feature Composables
- Содержат бизнес-логику конкретного домена
- Используют shared composables для общей функциональности

#### Shared Composables
- `useNotifications` - Глобальные уведомления
- Не содержат доменной логики

### 🎨 Компоненты

#### Feature Компоненты
- Специфичны для конкретного домена
- Могут использовать shared компоненты

#### Shared Компоненты
- Переиспользуемые во всем приложении
- Не содержат доменной логики

## Миграция

Проект был мигрирован с плоской структуры на feature-driven архитектуру:

1. ✅ Перемещены все компоненты в правильную структуру
2. ✅ Исправлены все импорты
3. ✅ Создана документация для каждой feature
4. ✅ Настроены composables с правильной логикой

## Следующие шаги

1. 📝 Создать тесты для composables
2. 🎨 Стандартизировать компоненты в каждой feature
3. 📚 Расширить документацию
4. 🔧 Добавить type guards и валидацию
