# OneCar CRM System

A modern, secure, and scalable Enterprise CRM System for auto-dealers, built with Laravel 11.

## 🚀 Features

### Role-Based Access Control (RBAC)
- **Admin**: Full system access, user management, car CRUD, global SMS
- **Dealer**: Own cars management, wallet operations, financial reports
- **Client**: Read-only access to their assigned cars

### Security Features
- ✅ CSRF Protection on all forms
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ XSS Protection (Blade auto-escaping)
- ✅ Secure Session Management with regeneration
- ✅ Rate Limiting on login (5 attempts/minute)
- ✅ Security Headers (XSS, Clickjacking, HSTS)
- ✅ Environment-based configuration (no hardcoded secrets)

### Financial Module
- Safe wallet transactions with DB-level race condition prevention
- Atomic balance operations using `UPDATE ... WHERE balance >= amount`
- Full transaction history and audit trail

### Car Management
- Complete CRUD with status workflow
- Image uploads with compression and thumbnail generation
- Automated SMS notifications on status changes
- Multi-category photo organization (Auction, Port, Terminal)

### UI/UX
- Glassmorphism design system
- Responsive Bootstrap 5 layout
- Georgian language support

## 📁 Directory Structure

```
crm/
├── app/
│   ├── Enums/              # Type-safe enumerations
│   │   ├── UserRole.php
│   │   ├── CarStatus.php
│   │   └── TransactionPurpose.php
│   ├── Http/
│   │   ├── Controllers/    # Request handlers
│   │   ├── Middleware/     # Security & auth middleware
│   │   └── Requests/       # Form validation
│   ├── Models/             # Eloquent models
│   ├── Policies/           # Authorization policies
│   └── Services/           # Business logic services
├── config/                 # Application configuration
├── database/
│   └── migrations/         # Database schema
├── resources/views/        # Blade templates
├── routes/web.php          # Route definitions
└── public/css/             # Compiled assets
```

## 🛠 Installation

### Requirements
- PHP 8.2+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ (for asset compilation)

### Steps

1. **Clone and install dependencies**
```bash
cd crm
composer install
npm install && npm run build
```

2. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Edit `.env` file**
```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

SMS_API_KEY=your_sms_api_key
```

4. **Run migrations**
```bash
php artisan migrate
php artisan db:seed  # Optional: seed demo data
```

5. **Create storage link**
```bash
php artisan storage:link
```

6. **Start development server**
```bash
php artisan serve
```

## 🔐 Security Configuration

### Environment Variables
All sensitive data is stored in `.env`:
- Database credentials
- SMS API keys
- Application secrets

### Session Security
```php
// config/session.php
'secure' => true,        // HTTPS only cookies
'http_only' => true,     // No JS access
'same_site' => 'lax',    // CSRF protection
```

### Rate Limiting
```php
// Login: 5 attempts per minute per IP
// API: 60 requests per minute per user
```

## 📊 Database Schema

### Users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'dealer', 'client'),
    balance DECIMAL(12,2) DEFAULT 0,
    sms_enabled BOOLEAN DEFAULT TRUE
);
```

### Cars
```sql
CREATE TABLE cars (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    vin VARCHAR(17),
    status ENUM('purchased', 'warehouse', 'loaded', 'on_way', 'poti', 'green', 'delivered'),
    vehicle_cost DECIMAL(12,2),
    paid_amount DECIMAL(12,2)
);
```

## 🎨 Glassmorphism Design Tokens

```css
:root {
    --card-bg: rgba(30, 41, 59, 0.7);
    --glass-blur: blur(12px);
    --primary-blue: #3b82f6;
    --text-main: #f1f5f9;
}
```

## 📱 API Reference

### Authentication
```
POST /login          # Login with CSRF token
POST /logout         # Logout and invalidate session
```

### Cars
```
GET    /              # Dashboard (car list)
GET    /cars/{id}     # View car details
POST   /cars          # Create car (admin)
PUT    /cars/{id}     # Update car
DELETE /cars/{id}     # Delete car (admin)
```

### Wallet
```
GET  /wallet          # View balance & history
POST /wallet/transfer # Transfer to car payment
```

## 🔄 Car Status Workflow

```
purchased → warehouse → loaded → on_way → poti → green → delivered
```

Each status change triggers:
1. Database update
2. In-app notification
3. SMS notification (if enabled)

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## 📝 License

Proprietary - OneCar © 2024
