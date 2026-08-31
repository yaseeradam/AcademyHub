# AcademyHub Docker Deployment Guide
> Deploy AcademyHub as a hybrid offline + online school management system

## Prerequisites

| Requirement | Minimum | Recommended |
|:---|:---|:---|
| **RAM** | 4 GB | 8 GB |
| **Storage** | 10 GB free | 50 GB (for photos, report cards, etc.) |
| **OS** | Windows 10/11, Ubuntu 20.04+, macOS | Ubuntu 22.04 LTS |
| **Docker** | Docker Engine 24+ | Docker Desktop (Windows/Mac) or Docker Engine (Linux) |
| **Network** | Ethernet / Wi-Fi router | Static local IP configured |

---

## Step 1: Install Docker

### Windows
1. Download [Docker Desktop](https://www.docker.com/products/docker-desktop/)
2. Install and restart your PC
3. Open Docker Desktop and let it start

### Ubuntu / Linux
```bash
# Install Docker
curl -fsSL https://get.docker.com | sh

# Add your user to docker group (so you don't need sudo)
sudo usermod -aG docker $USER

# Log out and back in, then verify
docker --version
docker compose version
```

### macOS
1. Download [Docker Desktop for Mac](https://www.docker.com/products/docker-desktop/)
2. Install and start it

---

## Step 2: Get the Code

```bash
git clone https://github.com/yaseeradam/AcademyHub.git
cd AcademyHub
```

---

## Step 3: Configure Environment

```bash
# Copy the Docker environment template
cp .env.docker .env
```

Now edit `.env` with your school's details:

```bash
# Use nano, vim, or any text editor
nano .env
```

### Key settings to change:

```env
# Your school name
APP_NAME=AcademyHub
MYACADEMY_SCHOOL_NAME="Frontal Minds Academy"

# For LOCAL (offline) deployment:
APP_URL=http://192.168.1.100
# For CLOUD (online) deployment:
# APP_URL=https://yourdomain.com

# Database passwords (CHANGE THESE!)
DB_PASSWORD=your_secure_password_here
DB_ROOT_PASSWORD=your_root_password_here

# Admin login
MYACADEMY_ADMIN_EMAIL=admin@school.com
MYACADEMY_ADMIN_PASSWORD=your_admin_password

# Optional: WhatsApp (leave empty for offline-only)
WHATSAPP_API_KEY=

# Optional: AI remarks (leave empty for offline-only)
GROQ_API_KEY=
```

> [!IMPORTANT]
> Change `DB_PASSWORD` and `DB_ROOT_PASSWORD` to strong passwords before deploying!

---

## Step 4: Build & Start

```bash
# Build the Docker image and start all services
docker compose up -d --build
```

This will:
1. Build the PHP application image (first time takes ~5-10 minutes)
2. Start MySQL database
3. Start Redis cache
4. Run database migrations automatically
5. Generate application key
6. Cache config/routes/views

### Check if everything is running:
```bash
docker compose ps
```

You should see 3 containers running:
```
NAME                STATUS          PORTS
academyhub-app      Up (healthy)    0.0.0.0:80->80/tcp
academyhub-db       Up (healthy)    0.0.0.0:3306->3306/tcp
academyhub-redis    Up              
```

### Check startup logs:
```bash
docker compose logs app
```

You should see:
```
========================================
  AcademyHub - Starting Application
========================================
[1/6] Waiting for database connection...
  ✓ Database connected
[2/6] Generating application key...
  ✓ Application key generated
[3/6] Running database migrations...
  ✓ Migrations complete
[4/6] Ensuring storage symlink...
  ✓ Storage linked
[5/6] Caching configuration...
  ✓ Caches built
[6/6] Setting permissions...
  ✓ Permissions set
========================================
  AcademyHub is ready!
  Access: http://192.168.1.100
========================================
```

---

## Step 5: Access AcademyHub

### Local (Offline) Access
Open a browser on any device connected to the school network and go to:
```
http://192.168.1.100
```
(Replace with your server's actual local IP address)

### Find your server's local IP:
```bash
# Windows
ipconfig

# Linux/Mac
hostname -I
```

### Login with your admin credentials:
- **Email:** The email you set in `.env` (`MYACADEMY_ADMIN_EMAIL`)
- **Password:** The password you set in `.env` (`MYACADEMY_ADMIN_PASSWORD`)

---

## Step 6: Connect ZKTeco K40 (Optional)

If using biometric attendance:

1. On the ZKTeco K40 device, go to **Menu → Communication → ADMS Settings**
2. Set **Server Address** to your server's IP: `192.168.1.100`
3. Set **Server Port** to `80`
4. The device will automatically push attendance to AcademyHub

---

## Useful Commands

### Stop the system
```bash
docker compose down
```

### Restart the system
```bash
docker compose restart
```

### Update to latest version
```bash
git pull origin main
docker compose up -d --build
```

### View live logs
```bash
# All services
docker compose logs -f

# App only
docker compose logs -f app

# Database only
docker compose logs -f db
```

### Run Laravel artisan commands
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan cache:clear
docker compose exec app php artisan tinker
```

### Database backup
```bash
docker compose exec db mysqldump -u academyhub -p academyhub > backup_$(date +%Y%m%d).sql
```

### Database restore
```bash
docker compose exec -T db mysql -u academyhub -p academyhub < backup_20260831.sql
```

### Reset everything (WARNING: deletes all data)
```bash
docker compose down -v
docker compose up -d --build
```

---

## Cloud Deployment (Online)

For deploying on a VPS (DigitalOcean, Hostinger, AWS, etc.):

1. **Get a VPS** with Ubuntu 22.04, minimum 2GB RAM
2. **Point your domain** (DNS A record) to the VPS IP
3. **SSH into the server** and follow Steps 1-4 above
4. **Set `APP_URL`** to your domain in `.env`:
   ```env
   APP_URL=https://yourschool.com
   ```
5. **Add SSL** with a reverse proxy (Nginx on host or Caddy):
   ```bash
   # Install Caddy for automatic SSL
   sudo apt install -y caddy
   
   # Edit /etc/caddy/Caddyfile:
   yourschool.com {
       reverse_proxy localhost:80
   }
   
   sudo systemctl restart caddy
   ```

---

## Troubleshooting

| Issue | Solution |
|:---|:---|
| `port 80 already in use` | Change `APP_PORT=8080` in `.env`, then access via `http://192.168.1.100:8080` |
| `database connection refused` | Wait 30 seconds for MySQL to start, then check: `docker compose logs db` |
| `permission denied` on storage | Run: `docker compose exec app chown -R www-data:www-data storage` |
| `out of memory` during build | Increase Docker Desktop memory limit (Settings → Resources → Memory → 4GB+) |
| App shows blank page | Run: `docker compose exec app php artisan config:clear && docker compose restart app` |
