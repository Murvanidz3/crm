# FTP Deploy Troubleshooting - ფაილები არ გადაიტანება

## პრობლემა: Workflow წარმატებულია, მაგრამ ფაილები არ გადაიტანება

ეს შეიძლება იყოს რამდენიმე მიზეზი:

### 1. FTP Server Path-ის შემოწმება

GitHub Secrets-ში `FTP_SERVER` უნდა იყოს სწორი:
- **Hostinger-ისთვის:** `ftp.hostinger.com` ან `your-domain.com`
- **Port:** `21` (default) ან `22` (SFTP)

### 2. Server Directory Path-ის შემოწმება

Workflow-ში `server-dir: ./portal/` არის, რაც ნიშნავს FTP root-ის შიგნით `portal` ფოლდერში.

**Hostinger-ზე FTP root-ი ჩვეულებრივ არის:**
- `~/public_html/` (shared hosting)
- `~/domains/onecar.ge/public_html/` (domain-specific)

თუ FTP root არის `~/domains/onecar.ge/public_html/`, მაშინ:
- `server-dir: ./portal/` → `~/domains/onecar.ge/public_html/portal/` ✅

### 3. GitHub Secrets-ის შემოწმება

გადადით: https://github.com/Murvanidz3/crm/settings/secrets/actions

შეამოწმეთ:
- `FTP_SERVER` - სწორია?
- `FTP_USERNAME` - სწორია?
- `FTP_PASSWORD` - სწორია?

### 4. FTP Root Directory-ის განსაზღვრა

Hostinger-ის hPanel-ში:
1. Websites → FTP Accounts
2. იპოვეთ თქვენი FTP account
3. შეამოწმეთ "Home Directory" ან "FTP Root"

ეს არის FTP root, საიდანაც `server-dir` ითვლება.

### 5. Workflow Log-ის შემოწმება

GitHub Actions-ში:
1. გადადით: https://github.com/Murvanidz3/crm/actions
2. აირჩიეთ ბოლო workflow run
3. გადადით "Deploy CRM files" step-ში
4. შეამოწმეთ log-ი:
   - "Uploading:" - ფაილები იტვირთება?
   - "Creating folder:" - ფოლდერები იქმნება?
   - შეცდომები?

### 6. FTP Connection Test

SSH-ში შეგიძლიათ შეამოწმოთ FTP connection:

```bash
# FTP connection test (თუ ftp client არის)
ftp ftp.hostinger.com
# შეიყვანეთ username და password
# შემდეგ: pwd (მიმდინარე directory)
# ls (ფაილების სია)
```

### 7. Server Directory Path-ის შეცვლა

თუ FTP root არის `~/domains/onecar.ge/public_html/`, მაგრამ `portal` ფოლდერი არ არის იქ, შეიძლება საჭირო იყოს:

**ვარიანტი A:** შეცვალეთ `server-dir`:
```yaml
server-dir: ./domains/onecar.ge/public_html/portal/
```

**ვარიანტი B:** თუ FTP root არის `~/public_html/`:
```yaml
server-dir: ./portal/
```

**ვარიანტი C:** თუ FTP root არის `~/domains/onecar.ge/public_html/`:
```yaml
server-dir: ./portal/
```

### 8. Manual FTP Test

შეგიძლიათ გამოიყენოთ FTP client (FileZilla, WinSCP) და შეამოწმოთ:
- სად არის FTP root
- შეგიძლიათ თუ არა წვდომა `portal` ფოლდერზე
- შეგიძლიათ თუ არა ფაილების ატვირთვა

---

## რეკომენდაცია

1. შეამოწმეთ GitHub Actions workflow log-ი - რა არის "Uploading:" და "Creating folder:"?
2. შეამოწმეთ GitHub Secrets - სწორია FTP credentials?
3. შეამოწმეთ Hostinger FTP root directory
4. შეამოწმეთ, არის თუ არა `portal` ფოლდერი FTP root-ში

გამომიგზავნეთ:
- GitHub Actions workflow log-ის screenshot ან "Uploading:" ნაწილი
- Hostinger FTP root directory path
- GitHub Secrets-ის სტატუსი (არ არის საჭირო პაროლის გამოტანა)

