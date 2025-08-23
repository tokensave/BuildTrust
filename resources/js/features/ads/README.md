# 📢 Ads Domain - Домен объявлений

Этот домен содержит все компоненты, логику и типы, связанные с объявлениями.

## 📁 Структура

### `/components/` - Компоненты объявлений
- **cards/** - Карточки объявлений
  - `AdCard.vue` - Карточка объявления для публичного просмотра
  - `UserAdCard.vue` - Карточка объявления в личном кабинете пользователя
  
- **forms/** - Формы объявлений
  - `AdCreateForm.vue` - Форма создания объявления
  - `AdEditForm.vue` - Форма редактирования объявления
  - `AdFilters.vue` - Компонент фильтров объявлений
  - `FeaturesSelector.vue` - Селектор характеристик объявления
  
- **modals/** - Модальные окна
  - `AdGalleryModal.vue` - Просмотр галереи изображений
  - `AdDeleteConfirm.vue` - Подтверждение удаления объявления
  
- **display/** - Компоненты отображения данных
  - `AdDetails.vue` - Детальный просмотр объявления
  - `FeaturesDisplay.vue` - Отображение характеристик
  - `CategoryBreadcrumb.vue` - Хлебные крошки категорий

### `/composables/` - Композаблы для работы с объявлениями
- **useAds.ts** - Основная логика загрузки и управления объявлениями
- **useAdForm.ts** - Логика форм создания/редактирования
- **useAdFilters.ts** - Логика фильтрации объявлений
- **useAdActions.ts** - Действия с объявлениями (удаление, изменение статуса)

### `/types/` - Типы объявлений
- **ad.ts** - Основные типы объявлений
- **filters.ts** - Типы для фильтров
- **forms.ts** - Типы для форм

### `/utils/` - Утилиты объявлений
- **ad-helpers.ts** - Вспомогательные функции
- **ad-validation.ts** - Специфическая валидация объявлений

## 🎯 Основные сценарии использования

### 1. Просмотр списка объявлений
```typescript
import { useAds } from '@/features/ads/composables/useAds'

const { ads, loading, loadAds } = useAds()
await loadAds()
```

### 2. Создание объявления
```typescript
import { useAdForm } from '@/features/ads/composables/useAdForm'

const { form, submitCreate } = useAdForm()
await submitCreate()
```

### 3. Фильтрация объявлений
```typescript
import { useAdFilters } from '@/features/ads/composables/useAdFilters'

const { filters, applyFilters } = useAdFilters()
applyFilters({ type: 'goods', category: 'materials' })
```

## 📋 Компоненты

### AdCard.vue
Универсальная карточка объявления для публичного просмотра.

**Props:**
- `ad: Ad` - объект объявления
- `variant?: 'grid' | 'list'` - вариант отображения

### UserAdCard.vue  
Карточка объявления для личного кабинета с возможностью редактирования.

**Props:**
- `ad: UserAd` - объект объявления пользователя

**События:**
- `@edit` - редактирование объявления
- `@delete` - удаление объявления

### AdFilters.vue
Компонент фильтров с поддержкой всех типов фильтрации.

**Props:**
- `filters: AdFilters` - текущие фильтры
- `totalCount: number` - общее количество результатов

**События:**
- `@apply` - применение фильтров
- `@clear` - очистка фильтров

## 🔄 Интеграция с API

Все композаблы используют единый API client из `@/shared/lib/api.ts`

```typescript
// Получение объявлений
GET /api/ads
GET /api/ads/{id}

// Управление объявлениями
POST /api/user/{user}/ads
PUT /api/user/{user}/ads/{ad}
DELETE /api/user/{user}/ads/{ad}

// Фильтры
GET /api/filters/structure
```
