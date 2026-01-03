# Admin User-ის შექმნა

## ვარიანტი 1: Laravel Seeders-ის გამოყენება (რეკომენდებული)

### SSH წვდომით Hostinger-ზე

თუ გაქვთ SSH წვდომა Hostinger-ზე:

1. **დააკონფიგურირეთ `.env` ფაილი:**
   
   Hostinger-ზე `portal` ფოლდერში შექმენით `.env` ფაილი (თუ არ არსებობს):
   
   ```env
   APP_NAME=CRM
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=crm_db
   DB_USERNAME=crm_db
   DB_PASSWORD=Tormeti21!
   ```

2. **გენერირება APP_KEY:**
   ```bash
   php artisan key:generate
   ```

3. **გაუშვით seeders:**
   ```bash
   php artisan db:seed
   ```

   ან კონკრეტულად DatabaseSeeder:
   ```bash
   php artisan db:seed --class=DatabaseSeeder
   ```

### SSH წვდომის გარეშე (phpMyAdmin-ის მეშვეობით)

თუ SSH წვდომა არ გაქვთ, გამოიყენეთ ქვემოთ მოცემული SQL script.

---

## ვარიანტი 2: SQL Script-ის გამოყენება (phpMyAdmin)

თუ SSH წვდომა არ გაქვთ, შეგიძლიათ პირდაპირ SQL-ით შექმნათ admin user:

### ნაბიჯები:

1. **გადადით Hostinger-ის phpMyAdmin-ში:**
   - hPanel → Database → phpMyAdmin
   - აირჩიეთ `crm_db` ბაზა

2. **დააჭირეთ "SQL" ტაბს**

3. **ჩასვით შემდეგი SQL კოდი:**

```sql
-- Admin User-ის შექმნა
-- Username: admin
-- Password: admin123

INSERT INTO `users` (
    `username`, 
    `full_name`, 
    `phone`, 
    `password`, 
    `role`, 
    `balance`, 
    `sms_enabled`, 
    `created_at`, 
    `updated_at`
) VALUES (
    'admin', 
    'Administrator', 
    '', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin', 
    0.00, 
    1, 
    NOW(), 
    NOW()
);
```

4. **დააჭირეთ "Go"**

### Admin მონაცემები:

- **Username:** `admin`
- **Password:** `admin123`
- **Role:** `admin`

⚠️ **მნიშვნელოვანი:** პირველ შესვლაზე შეცვალეთ პაროლი!

---

## ვარიანტი 3: SQL ფაილის იმპორტი

შეგიძლიათ გამოიყენოთ `create_admin_user.sql` ფაილი (იხილეთ ქვემოთ).

---

## პრობლემების გადაჭრა

**პრობლემა:** "Duplicate entry 'admin' for key 'users_username_unique'"
**გადაწყვეტა:** Admin user უკვე არსებობს. შეგიძლიათ გამოიყენოთ არსებული ან წაშალოთ და თავიდან შექმნათ.

**პრობლემა:** Password hash არ მუშაობს
**გადაწყვეტა:** გამოიყენეთ Laravel seeders-ი, რომელიც ავტომატურად გენერირებს სწორ hash-ს.

**პრობლემა:** Laravel seeders არ მუშაობს
**გადაწყვეტა:** 
- დარწმუნდით, რომ `.env` ფაილი სწორადაა კონფიგურირებული
- შეამოწმეთ, რომ `APP_KEY` გენერირებულია: `php artisan key:generate`
- შეამოწმეთ database connection: `php artisan migrate:status`

