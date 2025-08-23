# Frontend Testing Guide

Этот проект использует **Vitest** + **Vue Test Utils** для тестирования Vue 3 + TypeScript приложения.

## Установленные пакеты

- **vitest** - быстрый тестовый фреймворк от создателей Vite
- **@vue/test-utils** - официальная библиотека для тестирования Vue компонентов
- **@testing-library/vue** - альтернативная библиотека с акцентом на accessibility и пользовательский опыт
- **@testing-library/jest-dom** - дополнительные matchers для DOM тестирования
- **happy-dom** - быстрая DOM среда для тестов
- **jsdom** - альтернативная DOM среда

## Команды для запуска тестов

```bash
# Запуск тестов в watch режиме
npm run test

# Одноразовый запуск всех тестов
npm run test:run

# Запуск с coverage отчетом
npm run test:coverage

# Запуск с UI интерфейсом
npm run test:ui
```

## Структура тестов

Тесты располагаются рядом с тестируемыми файлами и имеют расширение `.test.ts` или `.spec.ts`:

```
resources/js/
├── features/
│   ├── auth/
│   │   └── composables/
│   │       ├── useAuth.ts
│   │       └── useAuth.test.ts
│   └── ads/
│       └── components/
│           └── badges/
│               ├── StatusBadge.vue
│               └── StatusBadge.test.ts
├── shared/
│   ├── composables/
│   │   └── data/
│   │       ├── useEnums.ts
│   │       └── useEnums.test.ts
│   └── utils/
│       └── format.test.ts
└── test/
    ├── setup.ts
    └── README.md
```

## Типы тестов

### 1. Unit тесты для composables

Тестируют логику composables (useAuth, useEnums, etc.):

```typescript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useAuth } from './useAuth';

describe('useAuth', () => {
    it('should return user data when authenticated', () => {
        // Тест логики
    });
});
```

### 2. Component тесты

Тестируют Vue компоненты с их поведением:

```typescript
import { mount } from '@vue/test-utils';
import StatusBadge from './StatusBadge.vue';

describe('StatusBadge', () => {
    it('should render status badge with correct label', () => {
        const wrapper = mount(StatusBadge, {
            props: { status: 'active' },
        });
        expect(wrapper.text()).toBe('Активное');
    });
});
```

### 3. Utility function тесты

Тестируют утилитарные функции:

```typescript
describe('formatPrice', () => {
    it('should format price correctly', () => {
        expect(formatPrice(1000)).toBe('1 000,00 ₽');
    });
});
```

## Моки и заглушки

### Mock Inertia.js

В `setup.ts` уже настроены моки для Inertia.js:

```typescript
vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: { auth: { user: mockUser } }
    })),
    useForm: vi.fn(() => ({ ... })),
}));
```

### Mock API вызовы

Для тестирования composables, которые делают API вызовы:

```typescript
const mockAxios = { get: vi.fn() };
vi.mock('axios', () => ({ default: mockAxios }));

mockAxios.get.mockResolvedValue({ data: mockData });
```

### Mock других composables

```typescript
const mockUseEnums = vi.fn();
vi.mock('@/shared/composables/data/useEnums', () => ({
    useEnums: mockUseEnums,
}));
```

## Best Practices

### 1. Группировка тестов

Используйте `describe` для группировки связанных тестов:

```typescript
describe('useAuth', () => {
    describe('when authenticated', () => {
        it('should return user data', () => {});
        it('should show correct avatar', () => {});
    });

    describe('when not authenticated', () => {
        it('should return null user', () => {});
    });
});
```

### 2. Очистка моков

Всегда очищайте моки в `beforeEach`:

```typescript
beforeEach(() => {
    vi.clearAllMocks();
});
```

### 3. Тестирование edge cases

Проверяйте граничные случаи:

```typescript
it('should handle empty data', () => {
    // тест для пустых данных
});

it('should handle API errors', () => {
    // тест для ошибок API
});
```

### 4. Понятные названия тестов

Используйте описательные названия:

```typescript
// ✅ Хорошо
it('should return formatted price in Russian currency format', () => {});

// ❌ Плохо  
it('should work correctly', () => {});
```

## Полезные советы

### 1. Debugging тестов

Добавьте `console.log()` или используйте `wrapper.html()` для debug:

```typescript
it('should render correctly', () => {
    const wrapper = mount(Component);
    console.log(wrapper.html()); // Debug DOM
    expect(wrapper.text()).toBe('Expected text');
});
```

### 2. Асинхронные тесты

Используйте `await` для асинхронных операций:

```typescript
it('should load data from API', async () => {
    const { loadEnums } = useEnums();
    await loadEnums();
    expect(mockAxios.get).toHaveBeenCalled();
});
```

### 3. Тестирование событий

```typescript
it('should emit event on click', async () => {
    const wrapper = mount(Component);
    await wrapper.find('button').trigger('click');
    expect(wrapper.emitted('click')).toBeTruthy();
});
```

## Coverage

Для получения отчета о покрытии кода тестами:

```bash
npm run test:coverage
```

Отчет будет создан в папке `coverage/`.

## Полезные ссылки

- [Vitest Documentation](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Library Vue](https://testing-library.com/docs/vue-testing-library/intro/)
- [Jest DOM Matchers](https://github.com/testing-library/jest-dom)
