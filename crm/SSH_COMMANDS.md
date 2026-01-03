# SSH ბრძანებები Hostinger-ზე

გაუშვით ეს ბრძანებები თანმიმდევრობით:

## 1. შეამოწმეთ სად ხართ

```bash
pwd
```

## 2. შეამოწმეთ რა ფოლდერები არის

```bash
ls -la
```

## 3. მოძებნეთ public_html ან domains

```bash
ls -la ~/public_html 2>/dev/null || ls -la ~/domains 2>/dev/null
```

## 4. შეამოწმეთ GitHub Actions-ის deploy-ის შედეგი

GitHub Actions workflow-ში ჩვენ დავაყენეთ `server-dir: ./portal/`, რაც ნიშნავს, რომ ფაილები უნდა იყოს FTP root-ის შიგნით `portal` ფოლდერში.

FTP root-ი ჩვეულებრივ არის:
- `~/public_html/` (shared hosting)
- `~/domains/yourdomain.com/public_html/` (domain-specific)

## 5. შექმენით portal ფოლდერი (თუ არ არსებობს)

**ვარიანტი A: public_html-ში**

```bash
mkdir -p ~/public_html/portal
cd ~/public_html/portal
ls -la
```

**ვარიანტი B: home directory-ში**

```bash
mkdir -p ~/portal
cd ~/portal
ls -la
```

## 6. შეამოწმეთ GitHub Actions-ის deploy-ის შედეგი

თუ GitHub Actions workflow უკვე გაეშვა, შეამოწმეთ:

```bash
find ~ -type d -name "crm" 2>/dev/null
```

ან

```bash
find ~ -name "composer.json" -path "*/crm/*" 2>/dev/null
```

## 7. FTP Root-ის განსაზღვრა

Hostinger-ის FTP settings-ში შეამოწმეთ:
- FTP Root Directory
- ან Hostinger-ის hPanel-ში: Websites → File Manager → დააჭირეთ "Go to File Manager"

ეს გაჩვენებთ, სად არის თქვენი ფაილები.

---

## რეკომენდაცია

1. გადადით Hostinger-ის hPanel-ში
2. Websites → File Manager
3. იპოვეთ სად არის თქვენი domain-ის ფაილები
4. იქ შექმენით `portal` ფოლდერი (თუ არ არსებობს)
5. GitHub Actions workflow-ში `server-dir` შეცვალეთ შესაბამისად

ან

თუ GitHub Actions-მა უკვე გადაიტანა ფაილები, იპოვეთ სად არის `crm` ფოლდერი და იქიდან იმუშავეთ.

