# AcademyHub

AcademyHub is a comprehensive cloud-based School Management System built with Laravel.

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
- `student`: Student portal (homework, results, attendance)
- `parent`: Parent portal (view children's data)

## Production Deployment

1) Install dependencies

```powershell
php .\composer.phar install
npm install
```

2) Configure `.env`

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL` to your domain
- Configure MySQL credentials (`DB_*`)
- Set mail credentials for notifications
- Set admin credentials:
  - `MYACADEMY_ADMIN_EMAIL`
  - `MYACADEMY_ADMIN_PASSWORD`

3) Migrate + seed

```powershell
php artisan migrate --force
php artisan db:seed --force
```

4) Build assets

```bash
npm run build
```

5) Configure web server (Nginx/Apache) and SSL certificate

6) Set proper permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

7) Optimize for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Features

- Student & Staff Management
- Academic Records & Results
- Attendance Tracking
- Fee Management
- Report Cards & Certificates
- **Student Portal** (Homework, Results, Attendance)
- **Parent Portal** (View children's progress)
- Homework Assignment & Submission
- WhatsApp Bot Integration
- Backup & Restore
- Multi-role Access Control

## Pricing

AcademyHub is a paid cloud-based service. Contact us for pricing and subscription plans.

## Demo Accounts (seeded)

- `admin@academyhub.local` / `password`
- `bursar@academyhub.local` / `password`
- `teacher@academyhub.local` / `password`
- Student login: Use admission number (e.g., `STU20240001`) with password format: `firstname + last4digits` (e.g., `john0001`)

## Backup & Restore (Admin)

Settings → Backup & Restore:

- **Backup Now:** creates `database.sql` (mysqldump) + zips it with `public/uploads`
- **Restore:** uploads a backup zip, enters maintenance mode, wipes DB + uploads, then restores

If `mysqldump` / `mysql` are not in PATH on your server, set:

- `MYACADEMY_MYSQLDUMP` (example: `/usr/bin/mysqldump`)
- `MYACADEMY_MYSQL` (example: `/usr/bin/mysql`)

## Server Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js 18+ & NPM
- GD Extension (for certificates)
- SSL Certificate (recommended)

## Support

For technical support and inquiries, contact the AcademyHub team.

---

© 2024 AcademyHub - Cloud School Management System
