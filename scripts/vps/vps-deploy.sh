#!/bin/bash

# MyAcademy Automated Deployment Script
# This script is triggered by GitHub Actions via SSH.

set -e

# --- Color Definitions ---
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

APP_DIR="/var/www/myacademy"

echo -e "${BLUE}Starting MyAcademy Deployment...${NC}"

# Navigate to application directory
cd $APP_DIR

# 1. Pull latest code from GitHub
echo -e "${YELLOW}Pulling latest code from git...${NC}"
git pull origin main

# 2. Deploy SMS Core (Laravel)
echo -e "${YELLOW}Deploying Laravel SMS Core...${NC}"

# Enable Maintenance Mode
php artisan down || true

# Install/update PHP dependencies
composer install --no-dev --optimize-autoloader

# Install/update Node dependencies
npm install

# Compile Vite assets
npm run build

# Run database migrations
php artisan migrate --force

# Clear and rebuild application caches
php artisan optimize
php artisan view:cache

# Disable Maintenance Mode
php artisan up

echo -e "${GREEN}Laravel SMS Core successfully deployed!${NC}"

# 3. Deploy WhatsApp Bot Extension
if [ -d "$APP_DIR/whatsapp-bot" ]; then
    echo -e "${YELLOW}Deploying WhatsApp Bot...${NC}"
    cd "$APP_DIR/whatsapp-bot"
    
    # Install dependencies
    npm install
    
    # Restart WhatsApp Bot with PM2 (or start if not exists)
    if pm2 list | grep -q "myacademy-whatsapp-bot"; then
        pm2 restart myacademy-whatsapp-bot
    else
        pm2 start index.js --name "myacademy-whatsapp-bot"
    fi
    
    pm2 save
    echo -e "${GREEN}WhatsApp Bot successfully deployed and restarted!${NC}"
else
    echo -e "${YELLOW}WhatsApp Bot directory not found. Skipping.${NC}"
fi

# 4. Restart Background Queue Workers
echo -e "${YELLOW}Restarting Supervisor queue workers...${NC}"
if command -v supervisorctl &> /dev/null; then
    sudo supervisorctl restart myacademy-worker:* || echo "Could not restart supervisor queue workers. Ensure supervisor is running."
fi

echo -e "${GREEN}===============================================${NC}"
echo -e "${GREEN}       Deployment Completed Successfully!       ${NC}"
echo -e "${GREEN}===============================================${NC}"
