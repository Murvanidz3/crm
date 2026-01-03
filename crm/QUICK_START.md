# 🚀 სწრაფი დაწყება - Hostinger-ზე

## ✅ რა გაკეთდა უკვე:

1. ✅ SQL ბაზა: `crm_db`
2. ✅ Database user: `crm_db` / `Tormeti21!`
3. ✅ SQL dump იმპორტირებულია
4. ✅ Admin user: `admin` / `admin123`
5. ✅ CRM ფაილები: `~/domains/onecar.ge/public_html/portal`

---

## 📋 შემდეგი ნაბიჯები:

### 1. გადადით portal ფოლდერში

```bash
cd ~/domains/onecar.ge/public_html/portal
pwd
ls -la
```

### 2. შექმენით `.env` ფაილი

```bash
cd ~/domains/onecar.ge/public_html/portal
nano .env
```

ან File Manager-ის მეშვეობით hPanel-ში.

ჩასვით:

```env
APP_NAME=CRM
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://onecar.ge

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crm_db
DB_USERNAME=crm_db
DB_PASSWORD=Tormeti21!
```

**💾 შეინახეთ:** `Ctrl+X`, შემდეგ `Y`, შემდეგ `Enter`

### 3. გენერირება APP_KEY

```bash
cd ~/domains/onecar.ge/public_html/portal
php artisan key:generate
```

ეს ავტომატურად განაახლებს `.env` ფაილში `APP_KEY`-ს.

### 4. Permissions-ების დაყენება

```bash
cd ~/domains/onecar.ge/public_html/portal
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 5. შესვლა სისტემაში

გადადით: **https://onecar.ge/login**

**მონაცემები:**
- Username: `admin`
- Password: `admin123`

⚠️ **პირველ შესვლაზე შეცვალეთ პაროლი!**

---

## 🔧 სწრაფი შემოწმება

```bash
cd ~/domains/onecar.ge/public_html/portal

# შეამოწმეთ .env ფაილი
cat .env | grep APP_KEY

# შეამოწმეთ database connection
php artisan migrate:status

# შეამოწმეთ storage permissions
ls -la storage
```

---

## ❓ პრობლემები?

### "No application encryption key"
```bash
cd ~/domains/onecar.ge/public_html/portal
php artisan key:generate
```

### "Connection refused"
შეამოწმეთ `.env` ფაილში database credentials.

### "Permission denied"
```bash
chmod -R 755 storage bootstrap/cache
```

---

## 🎉 მზადაა!

თუ ყველაფერი სწორად გაკეთდა, შეგიძლიათ შეხვიდეთ:
**https://onecar.ge/login**

