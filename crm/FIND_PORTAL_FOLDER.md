# Hostinger-ზე Portal ფოლდერის პოვნა

თუ `portal` ფოლდერი არ არსებობს, შეამოწმეთ:

## 1. შეამოწმეთ თქვენი home directory

```bash
cd ~
ls -la
```

ან

```bash
pwd
ls -la
```

## 2. მოძებნეთ CRM ფაილები

```bash
find ~ -name "composer.json" -type f 2>/dev/null
```

ან

```bash
find ~ -type d -name "crm" 2>/dev/null
```

## 3. შეამოწმეთ public_html ან domains ფოლდერი

Hostinger-ზე ხშირად აპლიკაციები არის:

```bash
cd ~/public_html
ls -la
```

ან

```bash
cd ~/domains
ls -la
```

## 4. GitHub Actions Deploy-ის შემოწმება

GitHub Actions workflow-ში ჩვენ დავაყენეთ:
- `server-dir: ./portal/`

ეს ნიშნავს, რომ ფაილები უნდა იყოს `portal` ფოლდერში FTP root-ის შიგნით.

## 5. FTP Root-ის პოვნა

FTP root-ი ჩვეულებრივ არის:
- `~/public_html/` (shared hosting-ისთვის)
- `~/domains/yourdomain.com/public_html/` (domain-ისთვის)

## რეკომენდაცია

თუ `portal` ფოლდერი არ არსებობს, შექმენით იგი:

```bash
mkdir -p ~/public_html/portal
cd ~/public_html/portal
```

ან თუ გსურთ სხვა ადგილას:

```bash
mkdir -p ~/portal
cd ~/portal
```

შემდეგ GitHub Actions workflow-ში შეცვალეთ `server-dir` შესაბამისად.

