# Portal.onecar.ge Debugging - შემოწმების ინსტრუქცია

## SSH-ში შეამოწმეთ:

### 1. ფაილების სტრუქტურა

```bash
cd ~/domains/onecar.ge/public_html/portal
ls -la
```

**რა უნდა იყოს:**
- ✅ `index.php` - root-ში (არა `public/index.php`)
- ✅ `.htaccess` - root-ში
- ✅ `artisan` - root-ში
- ✅ `composer.json` - root-ში
- ✅ `app/` ფოლდერი
- ✅ `vendor/` ფოლდერი
- ✅ `bootstrap/` ფოლდერი
- ✅ `config/` ფოლდერი

### 2. თუ `public/` ფოლდერი არის, გადაიტანეთ შიგთავსი root-ში

```bash
cd ~/domains/onecar.ge/public_html/portal

# შეამოწმეთ, არის თუ არა public ფოლდერი
ls -la public/ 2>/dev/null

# თუ არის, გადაიტანეთ შიგთავსი root-ში
if [ -d "public" ]; then
    mv public/* . 2>/dev/null || true
    mv public/.* . 2>/dev/null || true
    rmdir public 2>/dev/null || true
    echo "Public folder contents moved to root"
fi
```

### 3. .env ფაილის შემოწმება

```bash
cd ~/domains/onecar.ge/public_html/portal
ls -la .env
cat .env | grep APP_KEY
```

თუ `.env` არ არის ან `APP_KEY` ცარიელია:

```bash
nano .env
```

ჩასვით:
```env
APP_NAME=CRM
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://portal.onecar.ge

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crm_db
DB_USERNAME=crm_db
DB_PASSWORD=Tormeti21!
```

შემდეგ:
```bash
php artisan key:generate
```

### 4. Permissions-ების შემოწმება

```bash
chmod -R 755 storage bootstrap/cache
ls -la storage
ls -la bootstrap/cache
```

### 5. Vendor ფოლდერის შემოწმება

```bash
ls -la vendor/ 2>/dev/null || echo "Vendor folder not found"
```

თუ `vendor` არ არის:
```bash
composer install --no-dev --optimize-autoloader
```

### 6. PHP Error Log-ის შემოწმება

```bash
tail -n 50 ~/domains/onecar.ge/public_html/portal/storage/logs/laravel.log 2>/dev/null || echo "No log file"
```

### 7. Browser-ში შეცდომის შემოწმება

გადადით: https://portal.onecar.ge/login

**რა შეცდომაა?**
- 404 Not Found?
- 500 Internal Server Error?
- Blank page?
- სხვა შეცდომა?

### 8. index.php-ის შემოწმება

```bash
cd ~/domains/onecar.ge/public_html/portal
head -n 5 index.php
```

უნდა იყოს:
```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
```

### 9. .htaccess-ის შემოწმება

```bash
cat .htaccess
```

უნდა იყოს mod_rewrite rules.

---

## სწრაფი გადაწყვეტა (თუ public ფოლდერი არის):

```bash
cd ~/domains/onecar.ge/public_html/portal

# 1. გადაიტანეთ public-ის შიგთავსი root-ში
if [ -d "public" ]; then
    cp -r public/* . 2>/dev/null || true
    cp -r public/.* . 2>/dev/null || true
fi

# 2. შეამოწმეთ .env
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    # შექმენით .env (იხილეთ ზემოთ)
fi

# 3. გენერირება APP_KEY
php artisan key:generate

# 4. Permissions
chmod -R 755 storage bootstrap/cache

# 5. Composer install (თუ საჭიროა)
if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader
fi
```

---

## რა შეცდომა გამოჩნდა browser-ში?

გამომიგზავნეთ:
1. რა შეცდომაა (404, 500, blank page, და ა.შ.)
2. SSH-ში `ls -la` შედეგი
3. `tail -n 20 storage/logs/laravel.log` შედეგი (თუ არსებობს)

