# MyAcademy

## ⚡ QUICK START - Just 3 Steps!

1. **Install Laragon** - https://laragon.org/download/
2. **Copy files** to `C:\laragon\www\`
3. **Double-click** `MYACADEMY.bat` → Select option 1

**Done!** See `QUICK_START.md` for details.

---

MyAcademy is a comprehensive School Management System built with Laravel.

## Stack

- PHP `^8.2` (Laravel 11) with **GD extension** (required for certificates)
- Blade + Livewire v3
- Tailwind via Vite (local build)
- MySQL (InnoDB)
- PDF: `barryvdh/laravel-dompdf`

## Roles

- `admin`: full access + Settings + Backup/Restore
- `bursar`: Finance only
- `teacher`: Academics (scores / broadsheet)

## Local Setup (Windows / Laragon)

1) Enable GD extension (required for certificates)

```powershell
Double-click ENABLE_GD.bat
```

Then restart Laragon.

2) Install dependencies

```powershell
php .\composer.phar install
npm install
```

2) Configure `.env`

- Set MySQL credentials (`DB_*`)
- (Optional) set admin seed credentials:
  - `MYACADEMY_ADMIN_EMAIL`
  - `MYACADEMY_ADMIN_PASSWORD`

3) Migrate + seed

```powershell
php artisan migrate --force
php artisan db:seed --force
```

4) Build assets and run

```powershell
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

Open: `http://127.0.0.1:8000`

## Demo Accounts (seeded)

- `admin@myacademy.local` / `password`
- `bursar@myacademy.local` / `password`
- `teacher@myacademy.local` / `password`

## Backup & Restore (Admin)

Settings → Backup & Restore:

- **Backup Now:** creates `database.sql` (mysqldump) + zips it with `public/uploads`
- **Restore:** uploads a backup zip, enters maintenance mode, wipes DB + uploads, then restores

If `mysqldump` / `mysql` are not in PATH, set:

- `MYACADEMY_MYSQLDUMP` (example: `C:\Program Files\MySQL\MySQL Server 9.5\bin\mysqldump.exe`)
- `MYACADEMY_MYSQL` (example: `C:\Program Files\MySQL\MySQL Server 9.5\bin\mysql.exe`)

# MyAcademyV2
