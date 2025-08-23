# DDD Architecture Structure

## Структура доменов

```
app/
├── Domain/
│   ├── Company/
│   │   ├── Entities/           # Доменные сущности
│   │   ├── ValueObjects/       # Value Objects
│   │   ├── Services/           # Доменные сервисы
│   │   ├── Events/             # Доменные события
│   │   ├── Contracts/          # Интерфейсы репозиториев
│   │   └── Exceptions/         # Доменные исключения
│   ├── Ad/
│   ├── Deal/
│   ├── Chat/
│   ├── User/
│   └── AI/
├── Application/
│   ├── Company/
│   │   ├── Services/           # Application Services
│   │   ├── Commands/           # Command объекты
│   │   ├── Queries/            # Query объекты
│   │   └── Handlers/           # Command/Query handlers
│   ├── Ad/
│   ├── Deal/
│   ├── Chat/
│   ├── User/
│   └── AI/
├── Infrastructure/
│   ├── Company/
│   │   ├── Repositories/       # Реализации репозиториев
│   │   └── Services/           # Внешние сервисы
│   ├── Ad/
│   ├── Deal/
│   ├── Chat/
│   ├── User/
│   └── AI/
├── Shared/
│   ├── ValueObjects/           # Общие Value Objects
│   ├── Events/                 # Общие события
│   ├── Exceptions/             # Общие исключения
│   └── Services/               # Общие сервисы
└── Http/                       # Остается как есть
    ├── Controllers/
    └── Middleware/
```

## Принципы организации:

1. **Domain** - содержит чистую бизнес-логику
2. **Application** - оркестрирует домены
3. **Infrastructure** - техническая реализация
4. **Shared** - общие компоненты
