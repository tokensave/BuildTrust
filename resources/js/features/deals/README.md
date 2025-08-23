# 🤝 Deals Domain - Домен сделок

Этот домен содержит все компоненты и логику для работы со сделками между пользователями.

## 📁 Структура

### `/components/` - Компоненты сделок
- **cards/** - Карточки сделок
  - `DealCard.vue` - Карточка сделки в списке
  - `DealDetails.vue` - Детальный просмотр сделки
  
- **forms/** - Формы сделок
  - `DealCreateForm.vue` - Форма создания предложения сделки
  - `DealUpdateForm.vue` - Форма обновления условий сделки
  
- **modals/** - Модальные окна
  - `DealCreateModal.vue` - Модальное окно создания сделки
  - `DealStatusModal.vue` - Модальное окно изменения статуса
  - `DealDocumentsModal.vue` - Просмотр документов сделки
  
- **status/** - Компоненты статусов
  - `DealStatusBadge.vue` - Бейдж статуса сделки
  - `StatusTimeline.vue` - Временная линия изменений статусов

### `/composables/` - Композаблы для работы со сделками
- **useDeals.ts** - Основная логика загрузки и управления сделками
- **useDealForm.ts** - Логика форм создания/редактирования сделок
- **useDealActions.ts** - Действия со сделками (принятие, отклонение, завершение)
- **useDealStatus.ts** - Управление статусами сделок

### `/types/` - Типы сделок
- **deal.ts** - Основные типы сделок
- **status.ts** - Типы статусов сделок
- **forms.ts** - Типы для форм сделок

### `/utils/` - Утилиты сделок
- **deal-helpers.ts** - Вспомогательные функции
- **deal-validation.ts** - Валидация сделок

## 🎯 Основные сценарии использования

### 1. Просмотр списка сделок
```typescript
import { useDeals } from '@/features/deals/composables/useDeals'

const { deals, loading, loadDeals } = useDeals()
await loadDeals()
```

### 2. Создание предложения сделки
```typescript
import { useDealForm } from '@/features/deals/composables/useDealForm'

const { form, submitCreate } = useDealForm()
await submitCreate(adId, buyerId)
```

### 3. Изменение статуса сделки
```typescript
import { useDealActions } from '@/features/deals/composables/useDealActions'

const { acceptDeal, rejectDeal, completeDeal } = useDealActions()
await acceptDeal(dealId)
```

## 📋 Статусы сделок

### Жизненный цикл сделки:
1. **pending** - В ожидании (новое предложение)
2. **accepted** - Принята продавцом
3. **rejected** - Отклонена продавцом
4. **completed** - Завершена (успешно)
5. **canceled** - Отменена

### Права доступа:
- **Покупатель** - может создавать предложения, отменять свои сделки
- **Продавец** - может принимать/отклонять, завершать сделки
- **Обе стороны** - могут просматривать детали и документы

## 🔄 Интеграция с API

```typescript
// Управление сделками
GET /api/deals              // Список сделок пользователя
GET /api/deals/{id}         // Детали сделки
POST /api/deals/{ad}        // Создание предложения
PUT /api/deals/{id}/status  // Изменение статуса
DELETE /api/deals/{id}      // Отмена сделки

// Документы сделки
GET /api/deals/{id}/documents
POST /api/deals/{id}/documents
DELETE /api/deals/{id}/documents/{documentId}
```

## 📈 Блокчейн интеграция

Сделки могут быть записаны в блокчейн для обеспечения прозрачности:

```typescript
import { useDealBlockchain } from '@/features/deals/composables/useDealBlockchain'

const { sendToBlockchain, getBlockchainStatus } = useDealBlockchain()
await sendToBlockchain(dealId)
```
