#!/bin/bash

# MyAcademy Fresh VPS Installation Script
# This script must be run as root or with sudo privileges on Ubuntu 20.04+

set -e

# --- Color Definitions ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}===============================================${NC}"
echo -e "${BLUE}        MyAcademy VPS Setup Bootstrapper       ${NC}"
echo -e "${BLUE}===============================================${NC}"

# 1. Check if running as root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}Error: This script must be run as root or using sudo.${NC}"
  exit 1
fi

# 2. Update System Packages
echo -e "${YELLOW}Step 1: Updating system packages...${NC}"
apt-get update && apt-get upgrade -y

# Install basic helper utilities
apt-get install -y curl git unzip wget gnupg software-properties-common ca-certificates lsb-release ufw fail2ban

# 3. Add PHP Repository
echo -e "${YELLOW}Step 2: Adding PHP Ondřej Surý PPA repository...${NC}"
add-apt-repository -y ppa:ondrej/php
apt-get update

# 4. Install PHP 8.2 and common extensions
echo -e "${YELLOW}Step 3: Installing PHP 8.2 & required extensions...${NC}"
apt-get install -y \
  php8.2 \
  php8.2-cli \
  php8.2-fpm \
  php8.2-common \
  php8.2-mysql \
  php8.2-gd \
  php8.2-mbstring \
  php8.2-xml \
  php8.2-curl \
  php8.2-zip \
  php8.2-bcmath \
  php8.2-sqlite3 \
  php8.2-intl \
  php8.2-opcache \
  php8.2-readline

# Verify PHP installation
php -v

# 5. Install Node.js (v20 LTS)
echo -e "${YELLOW}Step 4: Installing Node.js v20 (LTS)...${NC}"
mkdir -p /etc/apt/keyrings
curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
apt-get update
apt-get install -y nodejs

# Verify Node.js and npm
node -v
npm -v

# 6. Install MySQL Server
echo -e "${YELLOW}Step 5: Installing MySQL Server...${NC}"
apt-get install -y mysql-server

# Start and enable MySQL service
systemctl start mysql
systemctl enable mysql

# 7. Install Nginx
echo -e "${YELLOW}Step 6: Installing Nginx...${NC}"
apt-get install -y nginx
systemctl start nginx
systemctl enable nginx

# 8. Install Composer (Globally)
echo -e "${YELLOW}Step 7: Installing Composer...${NC}"
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
composer --version

# 9. Install PM2 (Globally)
echo -e "${YELLOW}Step 8: Installing PM2...${NC}"
npm install -g pm2
pm2 --version

# 10. Install Supervisor (for Laravel Queue Workers)
echo -e "${YELLOW}Step 9: Installing Supervisor...${NC}"
apt-get install -y supervisor
systemctl start supervisor
systemctl enable supervisor

# 11. Install Headless Chromium & Puppeteer Dependencies
# These are required by Spatie Browsershot (PDF reports) and the WhatsApp Web bot.
echo -e "${YELLOW}Step 10: Installing Headless Chromium and system libraries...${NC}"
apt-get install -y chromium-browser --no-install-recommends || true

# Install all standard system dependencies needed by Puppeteer's headless Chrome
apt-get install -y \
  libgbm-dev \
  libnss3 \
  libatk-bridge2.0-0 \
  libgtk-3-0 \
  libasound2 \
  libxss1 \
  libgconf-2-4 \
  libxrender1 \
  libxtst6 \
  fonts-liberation \
  libappindicator3-1 \
  xdg-utils

# 12. Directory Structure and Permissions Setup
echo -e "${YELLOW}Step 11: Configuring application directories...${NC}"
mkdir -p /var/www/myacademy
chown -R www-data:www-data /var/www/myacademy
chmod -R 775 /var/www/myacademy

# Configure git to allow www-data to pull and run commands safely (prevents 'dubious ownership' errors)
git config --global --add safe.directory /var/www/myacademy

# 13. Basic Firewall Setup (UFW)
echo -e "${YELLOW}Step 12: Configuring firewall (UFW)...${NC}"
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
echo "y" | ufw enable
ufw status

echo -e "${GREEN}===============================================${NC}"
echo -e "${GREEN}      Bootstrap Installation Completed!        ${NC}"
echo -e "${GREEN}===============================================${NC}"
echo -e "Your fresh VPS is now ready for deployment configurations."
echo -e "Next steps:"
echo -e " 1. Run standard MySQL security setup: ${YELLOW}mysql_secure_installation${NC}"
echo -e " 2. Create the 'myacademy' database and database user."
echo -e " 3. Copy Nginx & Supervisor configuration templates to their locations."
echo -e " 4. Install Let's Encrypt Certbot: ${YELLOW}apt install certbot python3-certbot-nginx${NC}"
echo -e " 5. Register your domain name and point it to this server's IP."
