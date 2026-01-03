# 🚀 Portal.onecar.ge - სრული Setup ინსტრუქცია

## ✅ რა გაკეთდა უკვე:

1. ✅ GitHub Actions workflow მუშაობს
2. ✅ ყველა ფაილი გადაიტანება `public_html/portal/` ფოლდერში
3. ✅ Portal ფოლდერი გასუფთავდა და ახლიდან აიტვირთა

---

## 📋 Setup ნაბიჯები (SSH-ში):

### 1. გადადით portal ფოლდერში

```bash
cd ~/domains/onecar.ge/public_html/portal
pwd
ls -la
```

**უნდა იყოს:**
- ✅ `index.php`
- ✅ `.htaccess`
- ✅ `app/`, `bootstrap/`, `config/`, `routes/`, `database/`, `resources/`
- ✅ `composer.json`
- ✅ `artisan`

### 2. public/ ფოლდერის შიგთავსის root-ში გადატანა

რადგან subdomain-ის document root არის `portal/`, `public/`-ის შიგთავსი უნდა იყოს root-ში:

```bash
cd ~/domains/onecar.ge/public_html/portal

# შეამოწმეთ, არის თუ არა public ფოლდერი
if [ -d "public" ]; then
    echo "Moving public folder contents to root..."
    # გადაიტანეთ ფაილები
    mv public/* . 2>/dev/null || true
    mv public/.htaccess . 2>/dev/null || true
    # წაშალეთ public ფოლდერი
    rmdir public 2>/dev/null || rm -rf public
    echo "✅ Public folder contents moved to root"
else
    echo "✅ Public folder already moved or doesn't exist"
fi

# შეამოწმეთ
ls -la index.php
ls -la .htaccess
```

### 3. Composer Install (vendor ფოლდერისთვის)

```bash
cd ~/domains/onecar.ge/public_html/portal

# შეამოწმეთ, არის თუ არა vendor
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
    echo "✅ Composer install completed"
else
    echo "✅ Vendor folder already exists"
fi
```

### 4. .env ფაილის შექმნა

```bash
cd ~/domains/onecar.ge/public_html/portal

# შეამოწმეთ, არის თუ არა .env
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    nano .env
else
    echo ".env file already exists, editing..."
    nano .env
fi
```

**ჩასვით ეს კონტენტი:**

```env
APP_NAME=CRM
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://portal.onecar.ge

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crm_db
DB_USERNAME=crm_db
DB_PASSWORD=Tormeti21!

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

**💾 შეინახეთ:** `Ctrl+X`, `Y`, `Enter`

### 5. APP_KEY-ის გენერაცია

```bash
cd ~/domains/onecar.ge/public_html/portal
php artisan key:generate
```

ეს ავტომატურად განაახლებს `.env` ფაილში `APP_KEY`-ს.

### 6. Permissions-ების დაყენება

```bash
cd ~/domains/onecar.ge/public_html/portal
chmod -R 755 storage
chmod -R 755 bootstrap/cache
echo "✅ Permissions set"
```

### 7. შემოწმება

```bash
# შეამოწმეთ ყველაფერი
cd ~/domains/onecar.ge/public_html/portal

echo "=== File Structure ==="
ls -la | head -20

echo "=== .env Check ==="
cat .env | grep APP_KEY

echo "=== Storage Permissions ==="
ls -la storage | head -5

echo "=== Vendor Check ==="
ls -la vendor 2>/dev/null | head -5 || echo "Vendor not found"
```

---

## 🌐 Browser-ში შემოწმება

გადადით: **https://portal.onecar.ge/login**

**უნდა იყოს:**
- ✅ Login გვერდი
- ✅ არ არის 404 ან 500 შეცდომა

**Admin მონაცემები:**
- Username: `admin`
- Password: `admin123`

⚠️ **პირველ შესვლაზე შეცვალეთ პაროლი!**

---

## 🔧 პრობლემების გადაჭრა

### "404 Not Found"
- შეამოწმეთ, რომ `index.php` არის root-ში (არა `public/index.php`)
- შეამოწმეთ `.htaccess` ფაილი

### "500 Internal Server Error"
- შეამოწმეთ `.env` ფაილი
- შეამოწმეთ `APP_KEY` გენერირებულია თუ არა: `cat .env | grep APP_KEY`
- შეამოწმეთ `storage` და `bootstrap/cache` permissions: `ls -la storage`

### "Class not found" ან "Vendor autoload"
- გაუშვით: `composer install --no-dev --optimize-autoloader`
- შეამოწმეთ, რომ `vendor/` ფოლდერი არსებობს

### "No application encryption key"
- გაუშვით: `php artisan key:generate`

---

## ✅ შემოწმების სია

- [ ] `index.php` არის root-ში (არა `public/index.php`)
- [ ] `.htaccess` არის root-ში
- [ ] `vendor/` ფოლდერი არსებობს
- [ ] `.env` ფაილი არსებობს და კონფიგურირებულია
- [ ] `APP_KEY` გენერირებულია
- [ ] `storage` და `bootstrap/cache` permissions: 755
- [ ] https://portal.onecar.ge/login მუშაობს
- [ ] შეგიძლიათ შეხვიდეთ admin user-ით

---

## 🎉 მზადაა!

თუ ყველაფერი სწორად გაკეთდა, ახლა შეგიძლიათ:
- შეხვიდეთ CRM სისტემაში
- დაამატოთ მანქანები
- მართოთ მომხმარებლები
- ნახოთ ტრანზაქციები
- და ა.შ.

გაქვთ კითხვები? დაგვიკავშირდით!

