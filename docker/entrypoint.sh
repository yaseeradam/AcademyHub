#!/bin/sh
set -e

echo "========================================"
echo "  AcademyHub - Starting Application"
echo "========================================"

# ── Wait for MySQL ──────────────────────────────────────────────────────────
echo "[1/6] Waiting for database connection..."
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'OK'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "  Database not ready yet, retrying in 3s..."
    sleep 3
done
echo "  ✓ Database connected"

# ── Generate APP_KEY if missing ─────────────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "[2/6] Generating application key..."
    php artisan key:generate --force
    echo "  ✓ Application key generated"
else
    echo "[2/6] Application key already set ✓"
fi

# ── Run Migrations ─────────────────────────────────────────────────────────
echo "[3/7] Running database migrations..."
php artisan migrate --force 2>&1
echo "  ✓ Migrations complete"

# ── Seed superadmin & marketplace (safe to re-run — uses updateOrCreate) ──
echo "[4/7] Seeding superadmin account..."
php artisan db:seed --force 2>&1
echo "  ✓ Superadmin seeded"

# ── Storage link ────────────────────────────────────────────────────────────
echo "[5/7] Ensuring storage symlink..."
php artisan storage:link 2>/dev/null || true
echo "  ✓ Storage linked"

# ── Cache optimization ──────────────────────────────────────────────────────
echo "[6/7] Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "  ✓ Caches built"

# ── Fix permissions ─────────────────────────────────────────────────────────
echo "[7/7] Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
echo "  ✓ Permissions set"

echo "========================================"
echo "  AcademyHub is ready!"
echo "  Access: ${APP_URL:-http://localhost}"
echo "========================================"

# Execute the main command (supervisord)
exec "$@"
