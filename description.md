```
app/
├── DTOs/                    # Data Transfer Objects
│   └── GeoLocationData.php  # Іммутабельні об'єкти для передачі даних
├── Exceptions/              # Кастомні виключення
│   └── GeoLocationException.php
├── Http/
│   ├── Controllers/Api/     # API контролери
│   │   └── IpAddressController.php
│   ├── Requests/            # Валідація запитів
│   │   ├── StoreIpAddressRequest.php
│   │   └── UpdateIpAddressRequest.php
│   └── Resources/           # Форматування API відповідей
│       └── IpAddressResource.php
├── Models/                  # Eloquent моделі
│   ├── IpAddress.php
│   └── User.php
└── Services/                # Бізнес-логіка
    └── GeoLocationService.php
```

## 🔄 Як працює Request Flow

### 1. **Точка входу**: Route → Controller
```
HTTP Request → routes/api.php → IpAddressController
```

### 2. **Middleware Chain**:
```
Request → Sanctum Auth → Permission Check → Controller Method
```

### 3. **Controller обробка**:
```
Controller → Request Validation → Service → Model → Database
```

### 4. **Response Chain**:
```
Database → Model → Resource → JSON Response
```

## Компоненти

### **Request Classes** (Валідація)
```php
StoreIpAddressRequest::class
├── authorize() - Перевіряє дозволи користувача
├── rules() - Правила валідації даних
└── prepareForValidation() - Підготовка даних
```

**Завдання**: Валідувати вхідні дані та перевіряти права доступу

### **Controller** (Оркестратор)
```php
IpAddressController::class
├── index() - Список IP з фільтрами та пагінацією
├── store() - Створення нового IP + геолокація
├── show() - Отримання конкретного IP
├── update() - Оновлення геоданих
└── destroy() - Видалення IP
```

**Завдання**: Координувати взаємодію між компонентами, обробляти HTTP запити

### **Service Layer** (Бізнес-логіка)
```php
GeoLocationService::class
├── getGeoLocation() - Основний метод отримання геоданих
├── validateIpAddress() - Валідація IP адрес
├── checkRateLimit() - Контроль лімітів API
├── fetchFromApi() - Запит до зовнішнього API
└── Cache управління
```

**Завдання**: Інкапсулювати складну логіку роботи з зовнішнім API

### **DTO** (Data Transfer Objects)
```php
GeoLocationData::class (readonly)
├── Іммутабельний об'єкт
├── Типобезпечна передача даних
├── toArray() - Конвертація для БД
└── fromApiResponse() - Створення з API відповіді
```

**Завдання**: Забезпечити типобезпеку та структурованість даних

### **Model** (Data Layer)
```php
IpAddress::class extends Model
├── Eloquent ORM маппінг
├── Relationships (belongsTo User)
├── Scopes (byCountry, byCity)
├── Accessors (getLocationAttribute)
└── Casts (типізація атрибутів)
```

**Завдання**: Представляти дані в базі та надавати зручний інтерфейс

### **Resource** (Response Formatting)
```php
IpAddressResource::class
├── toArray() - Структурування відповіді
├── Conditional fields (whenLoaded, when)
├── Групування даних (location, coordinates)
└── Role-based visibility
```

**Завдання**: Форматувати вихідні дані для API

## Система безпеки

### **Authentication Flow**:
```
1. User login → AuthController::login()
2. Sanctum створює токен
3. Токен зберігається в БД (personal_access_tokens)
4. Client відправляє токен в Header: "Authorization: Bearer TOKEN"
5. Sanctum middleware валідує токен
```

### **Authorization Flow**:
```
1. Spatie Permission перевіряє ролі
2. User::can('permission') → bool
3. Контролер або Request блокує доступ якщо false
```

## Принципи архітектури

### **1. Single Responsibility Principle**
- Кожен клас має одну відповідальність
- Service тільки для геолокації
- Controller тільки для HTTP обробки
- Resource тільки для форматування

### **2. Dependency Injection**
```php
public function __construct(
    private readonly GeoLocationService $geoLocationService
) {}
```

### **3. Repository Pattern** (частково)
- Eloquent Model як Repository
- Scopes для складних запитів

### **4. DTO Pattern**
- Іммутабельні об'єкти для передачі даних
- Типобезпека та валідація структури

## Data Flow Example

### Створення IP адреси:

```
1. POST /api/v1/ip-addresses {"ip_address": "8.8.8.8"}
2. Sanctum middleware перевіряє токен
3. StoreIpAddressRequest валідує дані та права
4. IpAddressController::store() викликається
5. GeoLocationService::getGeoLocation("8.8.8.8")
   ├── Перевіряє rate limit
   ├── Шукає в кеші
   ├── Робить HTTP запит до ip-api.com
   └── Повертає GeoLocationData DTO
6. IpAddress::create() зберігає в БД
7. IpAddressResource форматує відповідь
8. JSON response повертається клієнту
```

## Переваги такої архітектури

### **Maintainability** (Підтримуваність)
- Чіткий розподіл відповідальностей
- Легко знайти де що знаходиться
- Зміни в одному компоненті не впливають на інші

### **Testability** (Тестабельність)
- Кожен компонент можна тестувати окремо
- Dependency Injection дозволяє мокати залежності
- Чітко визначені інтерфейси

### **Scalability** (Масштабованість)
- Легко додавати нові сервіси
- Можна винести Service в окремий мікросервіс
- Кешування зменшує навантаження

### **Security** (Безпека)
- Багаторівнева авторизація
- Валідація на кожному рівні
- Rate limiting захищає від зловживань

Ця архітектура слідує **Clean Architecture** принципам та **SOLID** принципам, що робить код підтримуваним та розширюваним!