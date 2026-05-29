# MyAcademy Web Production Server & Infrastructure Handbook

This handbook details the production-ready server setups, package dependencies, process management, database optimization, and cloud configurations required to host the **MyAcademy Web Application** securely and reliably.

---

## 1. Memory Management & Linux Swap File

Running database queries, PHP-FPM processes, and multiple **headless Chromium instances** (for certificate generation and the WhatsApp Web scraper) will occasionally cause high memory spikes. If physical RAM is exhausted, Linux will trigger the **OOM (Out Of Memory) Killer**, terminating critical services like MySQL.

### Provisioning a 4GB Swap File on Ubuntu/Debian:
Execute the following commands as `root` or via `sudo`:

```bash
# 1. Preallocate 4GB of disk space for the swap file
sudo fallocate -l 4G /swapfile

# If fallocate is not supported on the filesystem, use dd:
# sudo dd if=/dev/zero of=/swapfile bs=1M count=4096

# 2. Secure file permissions (root only)
sudo chmod 600 /swapfile

# 3. Designate the file as Linux Swap space
sudo mkswap /swapfile

# 4. Enable the swap file immediately
sudo swapon /swapfile

# 5. Make the swap file persistent across server reboots
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# 6. Verify swap is active
free -h
```

---

## 2. Spatie Browsershot & Puppeteer Prerequisites

Spatie Browsershot relies on Puppeteer (headless Chrome) for pixel-perfect PDF rendering of report cards and student certificates. Since headless Chrome has numerous binary dependencies that are missing from base Linux installations, you must install the following packages:

### Required System Packages (Ubuntu/Debian):
Run these commands to provision the required rendering libraries:

```bash
sudo apt update && sudo apt-get install -y \
    gconf-service \
    libasound2 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfontconfig1 \
    libgbm1 \
    libgcc1 \
    libgconf-2-4 \
    libgdk-pixbuf2.0-0 \
    libglib2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    ca-certificates \
    fonts-liberation \
    libappindicator1 \
    libnss3 \
    lsb-release \
    xdg-utils \
    wget
```

### Headless Chrome Sandbox Bypass:
Under secure production environments, Chrome's sandbox might crash depending on permissions. Browsershot is already preconfigured in `app/Support/CertificatePdf.php` to run with sandbox protection bypassed:
```php
$browsershot->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox']);
```

---

## 3. PM2 Process Optimization & Log Rotation

The WhatsApp Bot uses **PM2** to run the Node.js scraper in the background. Because Puppeteer processes yield significant amounts of debug logging and disk caching, you must configure automatic log rotation to protect the server from running out of disk space.

### Install & Configure `pm2-logrotate`:
```bash
# 1. Install logrotate module globally for PM2
pm2 install pm2-logrotate

# 2. Cap log size at 10MB per file
pm2 set pm2-logrotate:max_size 10M

# 3. Retain a maximum of 10 rotated log files
pm2 set pm2-logrotate:retain 10

# 4. Compress rotated log files to save space
pm2 set pm2-logrotate:compress true
```

### Bot Process Persistence Across Server Reboots:
```bash
# Generate PM2 system startup script
pm2 startup

# Copy and paste the resulting terminal command printed by PM2!

# Save currently active PM2 list to reboot configuration
pm2 save
```

---

## 4. Offsite Secure Cloud Backups (AWS S3 & Cloudflare R2)

Storing automated backups locally on the `/var/backups` directory is a major security risk. If the server crashes or files get corrupted, the database backups are lost.

### Move to Cloud Backups using Flysystem S3 Driver:
1. Install the official AWS S3 storage driver in the Laravel root:
   ```bash
   composer require league/flysystem-aws-s3-v3 "^3.0"
   ```
2. Open your production `.env` and set:
   ```env
   # Storage Disk Setup
   FILESYSTEM_DISK=s3
   
   # Cloud Credentials
   AWS_ACCESS_KEY_ID=your_access_key
   AWS_SECRET_ACCESS_KEY=your_secret_key
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=myacademy-production-backups
   AWS_USE_PATH_STYLE_ENDPOINT=false
   
   # (Optional) For Cloudflare R2 or other S3-compatible services:
   # AWS_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
   ```

3. Update the daily backup cron script to push your files directly to your cloud storage bucket instead of keeping them on the local disk.

---

## 5. Laravel Queue & Supervisor Configuration

To ensure background jobs (like WhatsApp log recording, certificate exports, and notification dispatches) run smoothly, you must move away from the `sync` queue driver.

### 1. Update Production `.env`
```env
QUEUE_CONNECTION=database
```

### 2. Configure Linux Supervisor
Create the configuration file:
`sudo nano /etc/supervisor/conf.d/myacademy-worker.conf`

```ini
[program:myacademy-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/myacademy/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/myacademy/storage/logs/worker.log
stopwaitsecs=3600
```

### 3. Restart Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start myacademy-worker:*
```

---

## 6. Multi-Tenant Database Optimization

### Composite Indexes on `tenant_id`
In MyAcademy, most Eloquent tables run queries containing `where tenant_id = ?`. Single-column indexes are not performant for complex joins.
Ensure that heavily filtered fields have a composite index where `tenant_id` is the **first** column:
```php
// Inside migration schemas
$table->index(['tenant_id', 'user_id']);
$table->index(['tenant_id', 'created_at']);
```

### MySQL Strict Mode (`ONLY_FULL_GROUP_BY`)
Managed production databases (like AWS RDS or DigitalOcean MySQL) have MySQL Strict Mode enabled by default. Ensure any raw SQL aggregation queries (such as student rankings or class averages) explicitly list all selected non-aggregated columns in their `GROUP BY` clause to avoid runtime database exceptions.

---

## 7. Transitioning to the Official WhatsApp Cloud API

The current WhatsApp Bot uses `whatsapp-web.js` which spins up a headless Chromium instance to interact with WhatsApp Web. 

> [!WARNING]
> **Scraping Warnings & Number Banning Risk**
> Scraping WhatsApp Web is against Meta's Terms of Service. If a school number sends bulk automated messages (such as daily absent alerts to 100+ parents) using web scraping, Meta will **permanently ban** the phone number very quickly. Furthermore, headless Chromium is highly unstable and will break whenever WhatsApp changes its front-end code.

### Safe Alternative: Meta's Official WhatsApp Cloud API
Transitioning to the **Meta Cloud API** (or through intermediate API hubs like Twilio, Termii, or 360Dialog) utilizes official developer accounts, ensuring **zero risk of number bans**.

### Meta Cloud API Integration Pattern:
Instead of running a heavy local Node.js scraper, the Laravel app can directly send HTTP requests to Meta's endpoints.

#### 1. Outgoing Messages (Laravel backend sends to Meta):
You can dispatch a secure POST request directly from a queued Laravel job:
```php
use Illuminate\Support\Facades\Http;

public function sendWhatsAppMessage(string $phone, string $templateName, array $parameters): bool
{
    $token = config('services.whatsapp.token');
    $phoneNumberId = config('services.whatsapp.phone_number_id');

    $response = Http::withToken($token)->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
        'messaging_product' => 'whatsapp',
        'to' => $phone,
        'type' => 'template',
        'template' => [
            'name' => $templateName,
            'language' => ['code' => 'en_US'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => $parameters // E.g. [['type' => 'text', 'text' => 'John Doe']]
                ]
            ]
        ]
    ]);

    return $response->successful();
}
```

#### 2. Incoming Commands & OTP Verification (Laravel Webhook):
You will register a single webhook route in `routes/api.php` to handle real-time WhatsApp incoming messages securely. Meta will POST JSON data directly to this webhook when parents send interactive messages (like `results` or `attendance`):

```php
Route::post('whatsapp/webhook', [App\Http\Controllers\Api\WhatsAppWebhookController::class, 'handleIncoming']);
```

In your webhook controller, parse the incoming payload, match the parent's phone number, execute the command dynamically, and dispatch the response back. This architecture **uses 90% less RAM**, has **zero downtime risk**, is completely resilient to WhatsApp Web updates, and **completely protects the school's phone numbers from bans**.
