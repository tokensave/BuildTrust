# 🚀 Frontend Architecture - Архитектура фронтенда

Проект построен на **Vue 3 + TypeScript + Inertia.js + ShadCN/UI** с доменно-ориентированной архитектурой.

## 📁 Общая структура

```
resources/js/
├── 📁 app/           # Основные настройки и глобальные типы
├── 📁 shared/        # Общие компоненты и утилиты
├── 📁 features/      # Функциональные домены (ads, deals, chat, auth)
├── 📁 pages/         # Inertia.js страницы
└── 📁 layouts/       # Макеты страниц
```

## 🏗️ Принципы архитектуры

### 1. 🎯 **Domain-Driven Design (DDD)**
Каждый функциональный домен (`ads`, `deals`, `chat`) изолирован и содержит:
- Компоненты
- Бизнес-логику (composables) 
- Типы TypeScript
- Утилиты

### 2. 🔄 **Shared vs Features**
- **Shared** - переиспользуемые компоненты без бизнес-логики
- **Features** - специфичные для домена компоненты с бизнес-логикой

### 3. 📦 **Composition API + Composables**
Вся логика вынесена в композаблы для переиспользования и тестируемости.

### 4. 🎨 **Design System**
Использование ShadCN/UI компонентов для консистентного дизайна.

## 🎯 Доменная структура

### 📢 **Ads Domain** (Объявления)
```typescript
import { useAds } from '@/features/ads/composables/useAds'
import AdCard from '@/features/ads/components/cards/AdCard.vue'
```

### 🤝 **Deals Domain** (Сделки)
```typescript
import { useDeals } from '@/features/deals/composables/useDeals'
import DealCard from '@/features/deals/components/cards/DealCard.vue'
```

### 💬 **Chat Domain** (Чат/Сообщения)
```typescript
import { useChat } from '@/features/chat/composables/useChat'
import ChatWindow from '@/features/chat/components/ChatWindow.vue'
```

### 🔐 **Auth Domain** (Аутентификация)
```typescript
import { useAuth } from '@/features/auth/composables/useAuth'
import LoginForm from '@/features/auth/components/LoginForm.vue'
```

## 🔧 Shared компоненты

### 🎨 **UI Components**
```typescript
import { Button } from '@/shared/ui/button'
import StatusBadge from '@/shared/components/feedback/StatusBadge.vue'
import FormField from '@/shared/components/forms/FormField.vue'
```

### ⚡ **Composables**
```typescript
import { useNotifications } from '@/shared/composables/core/useNotifications'
import { useEnums } from '@/shared/composables/data/useEnums'
import { formatPrice } from '@/shared/utils/formatting'
```

## 📝 Правила разработки

### 1. **Импорты с алиасами**
```typescript
// ✅ Правильно
import { useAds } from '@/features/ads/composables/useAds'
import { Button } from '@/shared/ui/button'

// ❌ Неправильно
import { useAds } from '../../features/ads/composables/useAds'
```

### 2. **Четкое разделение ответственности**
```typescript
// shared/ - только переиспользуемые компоненты
// features/ - только специфичная для домена логика
// pages/ - только компоненты страниц
```

### 3. **Типизация**
```typescript
// Каждый домен имеет свои типы
import type { Ad } from '@/features/ads/types/ad'
import type { Deal } from '@/features/deals/types/deal'
```

### 4. **Композаблы**
```typescript
// Один composable = одна ответственность
useAds()        // Загрузка и список объявлений
useAdForm()     // Формы объявлений 
useAdActions()  // Действия с объявлениями
```

## 🚀 Быстрый старт

### Создание нового компонента
```bash
# Создайте компонент в соответствующем домене
features/ads/components/cards/MyAdCard.vue

# Добавьте типы
features/ads/types/my-types.ts

# Создайте composable
features/ads/composables/useMyFeature.ts
```

### Создание нового домена
```bash
# Создайте структуру
features/my-domain/
  ├── components/
  ├── composables/
  ├── types/
  ├── utils/
  └── README.md
```

## 📚 Документация

Каждая папка содержит `README.md` с описанием:
- [Shared компоненты](./shared/README.md)
- [Ads домен](./features/ads/README.md) 
- [Deals домен](./features/deals/README.md)

## 🧪 Тестирование

```typescript
// Тестирование композаблов
import { useAds } from '@/features/ads/composables/useAds'

// Тестирование компонентов
import AdCard from '@/features/ads/components/cards/AdCard.vue'
```

## 🔄 Миграция с текущей структуры

1. **Сохраняем** `/pages` и `/layouts` как есть
2. **Перемещаем** компоненты в соответствующие домены
3. **Выносим** логику в composables
4. **Создаем** общие shared компоненты
5. **Обновляем** импорты
