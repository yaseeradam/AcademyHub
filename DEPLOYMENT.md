# MyAcademy Production Deployment Guide

## Overview

MyAcademy is a cloud-based School Management System. This guide covers production deployment on Linux servers.

## Local Multi-School (Recommended Dev Setup)
Use a main host + wildcard subdomains locally so multi-tenant routing matches production.

1) Pick a local main host (example `myacademy.test`) and add these to your Windows hosts file:
   - `127.0.0.1 myacademy.test`
   - `127.0.0.1 demo.myacademy.test`
2) Set `.env`:
   - `APP_URL=http://myacademy.test`
   - `SESSION_SECURE_COOKIE=false`
   - Optional: `SESSION_DOMAIN=.myacademy.test`
3) Bootstrap superadmin + demo tenant + tenant admin:
   - `php artisan myacademy:bootstrap-local --main-host=myacademy.test --tenant-slug=demo --tenant-name="Demo School"`
4) Login:
   - Superadmin: `http://myacademy.test/login` then `http://myacademy.test/superadmin`
   - School admin: `http://demo.myacademy.test/login`

## Server Requirements

### Minimum Specifications
- **OS**: Ubuntu 20.04+ or CentOS 8+
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 50GB SSD
- **PHP**: 8.2+
- **MySQL**: 8.0+ or MariaDB 10.6+
- **Node.js**: 18+
- **Web Server**: Nginx or Apache
- **Headless Chrome**: Chromium / Google Chrome (required for certificate PDF generation via Browsershot)

### Required PHP Extensions
- GD (for certificates)
- PDO MySQL
- Mbstring
- XML
- Curl
- OpenSSL
- JSON
- Tokenizer
- BCMath
- Ctype
- Fileinfo
- Zip

## Installation Steps

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-gd php8.2-mbstring \
php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-tokenizer

# Install MySQL
sudo apt install -y mysql-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx
```

### 2. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE myacademy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'myacademy_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON myacademy.* TO 'myacademy_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/myacademy
cd /var/www/myacademy

# Clone or upload application files
# (Upload via FTP/SFTP or git clone)

# Set ownership
sudo chown -R www-data:www-data /var/www/myacademy

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
# Ensure Vite dev marker isn't deployed
rm -f public/hot
npm run build

# Set permissions
sudo chmod -R 755 /var/www/myacademy
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env
```

**Required .env settings:**

```env
APP_NAME=MyAcademy
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myacademy
DB_USERNAME=myacademy_user
DB_PASSWORD=strong_password_here

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

MYACADEMY_ADMIN_EMAIL=admin@yourdomain.com
MYACADEMY_ADMIN_PASSWORD=secure_admin_password

MYACADEMY_MYSQLDUMP=/usr/bin/mysqldump
MYACADEMY_MYSQL=/usr/bin/mysql
```

### 5. Application Setup

```bash
# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Create storage link
php artisan storage:link
```

### 6. Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/myacademy
```

**Nginx configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/myacademy/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 100M;
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/myacademy /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### 7. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
```

### 8. Queue Worker Setup

```bash
# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/myacademy-worker.conf
```

```ini
[program:myacademy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/myacademy/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/myacademy/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start myacademy-worker:*
```

### 9. Cron Jobs

```bash
# Edit crontab
sudo crontab -e -u www-data
```

Add:

```cron
* * * * * cd /var/www/myacademy && php artisan schedule:run >> /dev/null 2>&1
```

## WhatsApp Bot Setup

### 1. Install PM2

```bash
sudo npm install -g pm2
```

### 2. Configure Bot

```bash
cd /var/www/myacademy/whatsapp-bot

# Install dependencies
npm install

# Start bot with PM2
pm2 start index.js --name myacademy-whatsapp-bot

# Save PM2 configuration
pm2 save

# Setup PM2 startup
pm2 startup
```

### 3. Monitor Bot

```bash
# View logs
pm2 logs myacademy-whatsapp-bot

# Check status
pm2 status

# Restart bot
pm2 restart myacademy-whatsapp-bot
```

## Security Hardening

### 1. Firewall Configuration

```bash
# Install UFW
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. Fail2Ban

```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Configure
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. File Permissions

```bash
# Secure permissions
sudo chown -R www-data:www-data /var/www/myacademy
sudo find /var/www/myacademy -type f -exec chmod 644 {} \;
sudo find /var/www/myacademy -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/myacademy/storage
sudo chmod -R 775 /var/www/myacademy/bootstrap/cache
```

## Backup Strategy

### Automated Backups

```bash
# Create backup script
sudo nano /usr/local/bin/myacademy-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/myacademy"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/myacademy"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u myacademy_user -p'password' myacademy > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $APP_DIR/storage/app/public $APP_DIR/public/uploads

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/myacademy-backup.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

Add:

```cron
0 2 * * * /usr/local/bin/myacademy-backup.sh >> /var/log/myacademy-backup.log 2>&1
```

## Monitoring

### Application Logs

```bash
# Laravel logs
tail -f /var/www/myacademy/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log
```

### Performance Monitoring

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Monitor resources
htop
```

## Maintenance

### Update Application

```bash
cd /var/www/myacademy

# Backup first
php artisan down

# Pull updates
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Bring back online
php artisan up
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Troubleshooting

### Permission Issues

```bash
sudo chown -R www-data:www-data /var/www/myacademy
sudo chmod -R 775 storage bootstrap/cache
```

### Queue Not Processing

```bash
sudo supervisorctl restart myacademy-worker:*
```

### WhatsApp Bot Not Working

```bash
pm2 restart myacademy-whatsapp-bot
pm2 logs myacademy-whatsapp-bot
```

### Database Connection Issues

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Support

For deployment assistance and technical support, contact the MyAcademy team.

---

© 2024 MyAcademy - Cloud School Management System
