# CRM Database Setup Instructions

## Hostinger-ზე SQL ბაზის დაყენება

### ვარიანტი 1: SQL Dump-ის იმპორტი (რეკომენდებული)

1. **Hostinger-ის hPanel-ში გადადით:**
   - Database → phpMyAdmin
   - ან Database → MySQL Databases

2. **შექმენით ახალი ბაზა** (თუ არ არსებობს):
   - Database name: `crm_db` (ან სხვა სახელი)
   - Character set: `utf8mb4_unicode_ci`

3. **იმპორტირება `database.sql` ფაილი:**
   - phpMyAdmin-ში აირჩიეთ თქვენი ბაზა
   - დააჭირეთ "Import" ტაბს
   - აირჩიეთ `database.sql` ფაილი
   - დააჭირეთ "Go"

4. **Admin User-ის შექმნა:**
   
   SQL dump-ში admin user-ის password hash არის placeholder. 
   რეკომენდებულია admin user-ის შექმნა Laravel seeders-ის მეშვეობით:
   
   ```bash
   php artisan db:seed
   ```
   
   ან თუ გსურთ SQL-ით პირდაპირ, შექმენით admin user:
   
   ```sql
   INSERT INTO `users` (`username`, `full_name`, `phone`, `password`, `role`, `balance`, `sms_enabled`, `created_at`, `updated_at`) 
   VALUES ('admin', 'Administrator', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00, 1, NOW(), NOW());
   ```
   
   **შენიშვნა:** პაროლი არის `admin123`. რეკომენდებულია პირველ შესვლაზე შეცვალოთ!

### ვარიანტი 2: Laravel Migrations-ის გამოყენება

თუ გაქვთ SSH წვდომა Hostinger-ზე:

1. **დააკონფიგურირეთ `.env` ფაილი:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

2. **გაუშვით migrations:**
   ```bash
   php artisan migrate
   ```

3. **გაუშვით seeders (admin user-ის შესაქმნელად):**
   ```bash
   php artisan db:seed
   ```

### Admin User-ის მონაცემები

- **Username:** `admin`
- **Password:** `admin123`
- **Role:** `admin`

⚠️ **მნიშვნელოვანი:** პირველ შესვლაზე შეცვალეთ პაროლი!

### ბაზის სტრუქტურა

SQL dump შეიცავს შემდეგ ცხრილებს:

- `users` - მომხმარებლები (admin, dealer, client)
- `cars` - მანქანები
- `transactions` - ტრანზაქციები
- `car_files` - მანქანების ფაილები (ფოტოები, ვიდეოები, დოკუმენტები)
- `notifications` - შეტყობინებები
- `sms_templates` - SMS შაბლონები
- `sms_logs` - SMS ლოგები
- `action_logs` - მოქმედებების ლოგები
- `migrations` - Laravel migrations ცხრილი
- `sessions` - სესიები
- `password_reset_tokens` - პაროლის აღდგენის ტოკენები

### SMS Templates

SQL dump ავტომატურად ქმნის SMS შაბლონებს ყველა სტატუსისთვის:
- purchased
- warehouse
- loaded
- on_way
- poti
- green
- delivered

### პრობლემების გადაჭრა

**პრობლემა:** Foreign key constraints errors
**გადაწყვეტა:** დარწმუნდით, რომ ყველა ცხრილი შეიქმნა სწორი თანმიმდევრობით. SQL dump-ში თანმიმდევრობა სწორია.

**პრობლემა:** Character encoding issues
**გადაწყვეტა:** დარწმუნდით, რომ ბაზა შეიქმნა `utf8mb4_unicode_ci` collation-ით.

**პრობლემა:** Admin user-ით შესვლა არ მუშაობს
**გადაწყვეტა:** გაუშვით `php artisan db:seed` admin user-ის სწორი password hash-ით შესაქმნელად.

