# GymTrack — Backend

Backend для цифровой замены бумажной тетради учёта в тренажерном зале. Приложение односторонее — один пользователь (владелец зала), без ролей и разграничения прав.

## Стек

- **PHP** 8.2+
- **Laravel** 11.x
- **MySQL**
- **Laravel Sanctum** — аутентификация мобильного клиента (токены)

## Функциональность

- Клиенты — учёт посетителей зала (ФИО, телефон)
- Типы абонементов — настраиваемые тарифы (месячный, разовый и т.д.)
- Продажи абонементов — фиксация оплаты
- Посещения — чек-ин по абонементу
- Отчёты — выручка, посещаемость за период

Подробное описание логики — в файле `TZ_GymTrack.md`.

## Установка

```bash
git clone <repo-url> gymtrack-backend
cd gymtrack-backend

composer install

cp .env.example .env
php artisan key:generate
```

Настройте подключение к БД в `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gymtrack
DB_USERNAME=root
DB_PASSWORD=
```

Установите Sanctum (если ещё не установлен) и примените миграции:

```bash
php artisan install:api
php artisan migrate
```

(Опционально) наполните базу тестовыми данными:

```bash
php artisan db:seed
```

Запуск локального сервера:

```bash
php artisan serve
```

API будет доступно по адресу `http://127.0.0.1:8000/api`.

## Архитектура

Проект построен на модулях (по одному на предметную область — Clients, Memberships, Visits, Reports, Auth), а внутри каждого модуля — на слоях с чёткими границами:

```
Request/Response → Controller (Api) → Service → Interface → Repository → Model
```

Как это работает:

- **Controller** принимает HTTP-запрос (уже провалидированный через Form Request) и вызывает нужный метод Service. Больше ничего — никакой бизнес-логики в контроллере.
- **Service** — бизнес-логика (например, расчёт даты окончания абонемента, проверка остатка визитов). Service ничего не знает про Eloquent — он обращается только к **Interface** репозитория, а не к конкретной реализации.
- **Interface** — контракт (`ClientRepositoryInterface` и т.п.), который описывает, какие методы доступны (`find`, `create`, `search`...), но не то, как они реализованы. Это даёт **инверсию зависимостей** (часть SOLID: класс должен зависеть от абстракции, а не от конкретной реализации) — Service можно тестировать с фейковым репозиторием, не трогая базу, а саму реализацию репозитория можно поменять (например, с MySQL на другой источник), не переписывая Service.
- **Repository** — конкретная реализация интерфейса, единственное место, где есть Eloquent-запросы к Model.
- **Model** — Eloquent-модель, просто описывает таблицу и связи.

Интерфейсы биндятся к своим реализациям в `RepositoryServiceProvider` (`app/Providers/RepositoryServiceProvider.php`), например:

```php
$this->app->bind(
    ClientRepositoryInterface::class,
    EloquentClientRepository::class
);
```

## Структура проекта

```
app/
├── Modules/
│   ├── Clients/
│   │   ├── Http/
│   │   │   ├── Controllers/ClientController.php
│   │   │   ├── Requests/StoreClientRequest.php
│   │   │   └── Resources/ClientResource.php
│   │   ├── Services/ClientService.php
│   │   ├── Repositories/
│   │   │   ├── ClientRepositoryInterface.php
│   │   │   └── EloquentClientRepository.php
│   │   └── Models/Client.php
│   │
│   ├── Memberships/
│   │   ├── Http/
│   │   │   ├── Controllers/MembershipTypeController.php
│   │   │   ├── Controllers/MembershipPurchaseController.php
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Services/
│   │   │   ├── MembershipTypeService.php
│   │   │   └── MembershipPurchaseService.php
│   │   ├── Repositories/
│   │   │   ├── MembershipTypeRepositoryInterface.php
│   │   │   ├── EloquentMembershipTypeRepository.php
│   │   │   ├── MembershipPurchaseRepositoryInterface.php
│   │   │   └── EloquentMembershipPurchaseRepository.php
│   │   └── Models/
│   │       ├── MembershipType.php
│   │       └── MembershipPurchase.php
│   │
│   ├── Visits/
│   │   ├── Http/Controllers/VisitController.php
│   │   ├── Services/VisitService.php
│   │   ├── Repositories/
│   │   │   ├── VisitRepositoryInterface.php
│   │   │   └── EloquentVisitRepository.php
│   │   └── Models/Visit.php
│   │
│   ├── Reports/
│   │   ├── Http/Controllers/ReportController.php
│   │   └── Services/ReportService.php
│   │
│   └── Auth/
│       └── Http/Controllers/AuthController.php
│
└── Providers/
    └── RepositoryServiceProvider.php   # биндинг Interface → Repository

database/
├── migrations/
└── seeders/
```

Модуль `Reports` не имеет своего репозитория — он читает данные через сервисы/репозитории других модулей (Memberships, Visits), а не напрямую через Eloquent, чтобы не дублировать логику доступа к данным.

## API

Аутентификация — `Bearer <token>` в заголовке `Authorization` для всех эндпоинтов, кроме `/login`.

| Метод | Путь | Описание |
|---|---|---|
| POST | `/api/login` | Вход владельца, получение токена |
| POST | `/api/logout` | Выход, инвалидация токена |
| GET | `/api/clients?search=` | Список/поиск клиентов |
| POST | `/api/clients` | Добавить клиента |
| GET | `/api/clients/{id}` | Карточка клиента (история визитов, оплат) |
| GET | `/api/membership-types` | Список типов абонементов |
| POST | `/api/membership-types` | Создать тип абонемента |
| PUT | `/api/membership-types/{id}` | Изменить тип абонемента |
| POST | `/api/membership-purchases` | Продать абонемент клиенту |
| POST | `/api/visits` | Зафиксировать визит (чек-ин) |
| GET | `/api/reports/revenue?from=&to=` | Отчёт по выручке |
| GET | `/api/reports/attendance?from=&to=` | Отчёт по посещаемости |

## Тесты

```bash
php artisan test
```

## Roadmap

MVP закрывает только базовый учёт (клиенты, абонементы, визиты, выручка). Автоматизация добавления клиентов, QR-чек-ин, уведомления и роли — вне рамок текущей версии (см. раздел 8 в `TZ_GymTrack.md`).
