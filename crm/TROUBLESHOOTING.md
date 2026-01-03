# პრობლემების გადაჭრა - Artisan ფაილი არ მოიძებნება

## პრობლემა: "Could not open input file: artisan"

ეს ნიშნავს, რომ `artisan` ფაილი არ არის `portal` ფოლდერში.

## გადაწყვეტა:

### 1. შეამოწმეთ, რა არის portal ფოლდერში

```bash
cd ~/domains/onecar.ge/public_html/portal
ls -la
```

### 2. მოძებნეთ artisan ფაილი

```bash
find ~/domains/onecar.ge -name "artisan" -type f 2>/dev/null
```

### 3. შეამოწმეთ, სად არის CRM ფაილები

```bash
find ~/domains/onecar.ge -name "composer.json" -type f 2>/dev/null
```

### 4. შეამოწმეთ GitHub Actions deploy-ის სტატუსი

1. გადადით GitHub-ზე: https://github.com/Murvanidz3/crm
2. დააჭირეთ "Actions" ტაბს
3. შეამოწმეთ, გაეშვა თუ არა "Deploy CRM to Hostinger" workflow
4. თუ გაეშვა, შეამოწმეთ, წარმატებით დასრულდა თუ არა

### 5. თუ GitHub Actions-მა ჯერ არ გადაიტანა ფაილები

**ვარიანტი A: ხელით გადატანა**

თუ გაქვთ SSH წვდომა, შეგიძლიათ Git-ის მეშვეობით:

```bash
cd ~/domains/onecar.ge/public_html
git clone https://github.com/Murvanidz3/crm.git temp_crm
mv temp_crm/crm portal
rm -rf temp_crm
cd portal
```

**ვარიანტი B: GitHub Actions-ის ხელით გაშვება**

1. გადადით GitHub-ზე: https://github.com/Murvanidz3/crm/actions
2. აირჩიეთ "Deploy CRM to Hostinger" workflow
3. დააჭირეთ "Run workflow" ღილაკს
4. აირჩიეთ "main" ბრენჩი
5. დააჭირეთ "Run workflow"

### 6. შეამოწმეთ FTP Deploy-ის server-dir

GitHub Actions workflow-ში `server-dir: ./portal/` არის, რაც ნიშნავს, რომ ფაილები უნდა იყოს FTP root-ის შიგნით `portal` ფოლდერში.

თუ FTP root არის `~/domains/onecar.ge/public_html/`, მაშინ ფაილები უნდა იყოს:
`~/domains/onecar.ge/public_html/portal/`

### 7. შეამოწმეთ, რა არის public_html-ში

```bash
cd ~/domains/onecar.ge/public_html
ls -la
```

თუ `portal` ფოლდერი არ არსებობს, შექმენით:

```bash
mkdir -p ~/domains/onecar.ge/public_html/portal
```

---

## რეკომენდაცია

1. შეამოწმეთ GitHub Actions-ის deploy-ის სტატუსი
2. თუ deploy წარმატებით დასრულდა, შეამოწმეთ, სად გადაიტანა ფაილები
3. თუ deploy არ გაეშვა, გაუშვით ხელით
4. ან გამოიყენეთ Git clone (ვარიანტი A ზემოთ)

