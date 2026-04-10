# SHAReS System — VPS Deployment Guide
### Hostinger KVM 2 · CloudPanel · Ubuntu 24.04

> **Target environment:** Hostinger KVM 2 VPS running CloudPanel on Ubuntu 24.04 LTS.  
> **Workflow:** Push from local → GitHub → Pull on VPS → Supervisor keeps Python APIs alive.

---

## Table of Contents
1. [Prerequisites & Credentials](#1-prerequisites--credentials)
2. [Connect to VPS](#2-connect-to-vps)
3. [System Package Setup](#3-system-package-setup)
4. [CloudPanel: Create PHP Site](#4-cloudpanel-create-php-site)
5. [Clone Repository from GitHub](#5-clone-repository-from-github)
6. [Laravel Application Setup](#6-laravel-application-setup)
7. [Configure the .env File](#7-configure-the-env-file)
8. [Database Setup](#8-database-setup)
9. [Build Frontend Assets](#9-build-frontend-assets)
10. [File Permissions & Storage](#10-file-permissions--storage)
11. [CloudPanel: Nginx Tweaks](#11-cloudpanel-nginx-tweaks)
12. [SSL Certificate](#12-ssl-certificate)
13. [LLM API Python Service Setup](#13-llm-api-python-service-setup)
14. [RandomForest API Python Service Setup](#14-randomforest-api-python-service-setup)
15. [Supervisor: Managing Both Python APIs](#15-supervisor-managing-both-python-apis)
16. [Laravel Queue Worker via Supervisor](#16-laravel-queue-worker-via-supervisor)
17. [GitHub → VPS Update Workflow](#17-github--vps-update-workflow)
18. [Health Checks & Verification](#18-health-checks--verification)
19. [Common Errors & Fixes](#19-common-errors--fixes)

---

## 1. Prerequisites & Credentials

| Item | Value |
|---|---|
| VPS IP | `<your-vps-ip>` |
| SSH User | `root` (or your sudo user) |
| SSH Port | `22` |
| Domain | `shares-app.site` |
| Laravel path | `/home/shares-app/htdocs/shares-app.site/` |
| LLM API port | `8002` |
| RandomForest API port | `8001` |
| DB Name | `capstone` (set in CloudPanel) |
| DB User | `capstone_user` |

**Have ready before starting:**
- Hostinger VPS root SSH password
- GitHub Personal Access Token (PAT) or SSH deploy key
- Groq API key
- Brevo SMTP credentials
- Cloudflare Turnstile keys (if enabled)

---

## 2. Connect to VPS

```bash
ssh root@<your-vps-ip>
```

Confirm OS:
```bash
lsb_release -a
# Should show Ubuntu 24.04
```

---

## 3. System Package Setup

Run these as **root** (or prefix with `sudo`):

```bash
# Update package list
apt update && apt upgrade -y

# Required tools
apt install -y git curl wget unzip supervisor nano htop

# PHP 8.2 + extensions (CloudPanel usually installs these, verify first)
# If missing:
apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd \
    php8.2-intl php8.2-tokenizer php8.2-pdo

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer --version

# Node.js 20.x (for Vite build)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
node --version && npm --version

# Python 3.11 + venv
apt install -y python3.11 python3.11-venv python3-pip
python3.11 --version
```

---

## 4. CloudPanel: Create PHP Site

1. Log in to CloudPanel: `https://<your-vps-ip>:8443`
2. Go to **Sites → + Create Site → PHP Site**
3. Fill in:
   - **Domain:** `shares-app.site`
   - **PHP Version:** `8.2`
   - **Document Root:** leave default → CloudPanel sets `/home/shares-app/htdocs/shares-app.site`
4. Create a **Database** in CloudPanel:
   - DB Name: `capstone`
   - DB User: `capstone_user`
   - DB Password: *(generate a strong one, save it)*
5. Note the credentials — you'll use them in the `.env`.

---

## 5. Clone Repository from GitHub

### Option A — HTTPS with Personal Access Token (recommended)

```bash
# Navigate to the parent directory
cd /home/shares-app/htdocs/

# Remove the blank directory CloudPanel created
rm -rf shares-app.site

# Clone directly INTO the site directory
git clone https://<your-github-pat>@github.com/<your-username>/Capstone-System.git shares-app.site

cd shares-app.site
```

> **Important:** The Laravel app lives inside `capstone_system/` in the repo.  
> The **document root must point to `capstone_system/public/`** — configure this next.

### What's inside after clone

```
/home/shares-app/htdocs/shares-app.site/
├── capstone_system/          ← Laravel app
│   ├── public/               ← Web root (Nginx points here)
│   ├── LLM/                  ← LLM FastAPI service
│   ├── RandomForest/         ← RandomForest FastAPI service
│   ├── .env.example
│   └── ...
├── DEPLOYMENT_GUIDE.md
└── README.md
```

### Update CloudPanel Document Root

1. CloudPanel → Sites → `shares-app.site` → **Settings**
2. Change **Document Root** to: `/home/shares-app/htdocs/shares-app.site/capstone_system/public`
3. Save.

---

## 6. Laravel Application Setup

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system

# Install PHP dependencies (no dev packages in production)
composer install --optimize-autoloader --no-dev

# Copy environment file
cp .env.production .env
# OR start fresh:
cp .env.example .env
```

---

## 7. Configure the .env File

```bash
nano /home/shares-app/htdocs/shares-app.site/capstone_system/.env
```

Fill in **all** the values below. Replace every `<placeholder>` with a real value:

```dotenv
# ─── APPLICATION ───────────────────────────────────────────────
APP_NAME="SHAReS System"
APP_ENV=production
APP_KEY=                          # leave blank now — generated next
APP_DEBUG=false
APP_URL=https://shares-app.site

# ─── LOGGING ───────────────────────────────────────────────────
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# ─── DATABASE ──────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capstone
DB_USERNAME=capstone_user
DB_PASSWORD=<your-db-password>

# ─── SESSION / QUEUE / CACHE ───────────────────────────────────
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database

# ─── MAIL (Brevo SMTP) ─────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=<your-brevo-email>
MAIL_PASSWORD=<your-brevo-smtp-key>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@shares-app.site
MAIL_FROM_NAME="${APP_NAME}"

# ─── PYTHON APIS (local microservices) ─────────────────────────
LLM_API_URL=http://127.0.0.1:8002
LLM_API_TIMEOUT=60
RANDOM_FOREST_API_URL=http://127.0.0.1:8001
RANDOM_FOREST_API_KEY=malnutrition-api-key-2025
RANDOM_FOREST_API_TIMEOUT=30

# ─── TURNSTILE (Cloudflare) ────────────────────────────────────
TURNSTILE_SITE_KEY=<your-turnstile-site-key>
TURNSTILE_SECRET_KEY=<your-turnstile-secret-key>

# ─── FEATURE FLAGS ─────────────────────────────────────────────
MAIL_VERIFICATION=true
```

**Generate the app key:**

```bash
php artisan key:generate
```

---

## 8. Database Setup

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system

# Run all migrations
php artisan migrate --force

# Run seeders (only if first-time, skip if importing existing DB dump)
php artisan db:seed --force
```

> If you have an existing database dump from a previous server, import it via CloudPanel's phpMyAdmin instead of seeding.

---

## 9. Build Frontend Assets

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system

# Install Node packages
npm ci --omit=dev

# Build production assets (Vite)
npm run build
```

This generates the `public/build/` directory used in production.

---

## 10. File Permissions & Storage

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system

# Set ownership to the web server user
chown -R www-data:www-data .

# Directories that must be writable
chmod -R 775 storage bootstrap/cache

# Create the storage:link for public disk
php artisan storage:link
```

---

## 11. CloudPanel: Nginx Tweaks

CloudPanel generates an Nginx config automatically. You need to add one rule so that Laravel handles all routes:

1. CloudPanel → Sites → `shares-app.site` → **Nginx Config**
2. Verify or add inside the `server {}` block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

3. Add gzip and file size tweaks at the top of the `server {}` block if needed:

```nginx
client_max_body_size 50M;
```

4. Save & **Rebuild/Restart Nginx** inside CloudPanel.

---

## 12. SSL Certificate

1. Make sure the DNS for `shares-app.site` and `www.shares-app.site` is pointing at your VPS IP.
2. CloudPanel → Sites → `shares-app.site` → **SSL Certificate → Let's Encrypt**
3. Enter both `shares-app.site` and `www.shares-app.site`
4. Click **Request Certificate**

If you're behind **Cloudflare**, set SSL Mode to **Full (Strict)** in the Cloudflare dashboard.

**Trust proxies** — make sure `app/Http/Middleware/TrustProxies.php` has:

```php
protected $proxies = '*';
protected $headers = Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

---

## 13. LLM API Python Service Setup

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system/LLM

# Create isolated virtualenv
python3.11 -m venv .venv

# Activate it
source .venv/bin/activate

# Install dependencies
pip install --upgrade pip
pip install -r requirements.txt

# Create the .env file for the LLM service
nano .env
```

Paste the following into `LLM/.env` (update values):

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=capstone
DB_USER=capstone_user
DB_PASSWORD=<your-db-password>

GROQ_API_KEY=<your-groq-api-key>

# FEEDING_PROGRAM_SEEDS_ENABLED=true
```

**Test it manually first:**

```bash
source .venv/bin/activate
uvicorn fastapi_app:app --host 127.0.0.1 --port 8002
# Hit Ctrl+C when confirmed working
```

Verify:
```bash
curl http://127.0.0.1:8002/
# Should return: {"message":"Meal Planning API is running", ...}
```

Deactivate when done testing:
```bash
deactivate
```

---

## 14. RandomForest API Python Service Setup

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system/RandomForest

# Create isolated virtualenv
python3.11 -m venv .venv

# Activate it
source .venv/bin/activate

# Install dependencies
pip install --upgrade pip
pip install -r requirements.txt

# Create the .env file for the RF service
nano .env
```

Paste the following into `RandomForest/.env`:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=capstone
DB_USER=capstone_user
DB_PASSWORD=<your-db-password>

SECRET_KEY=malnutrition-api-key-2025
API_KEY=malnutrition-api-key-2025
API_HOST=http://127.0.0.1
API_PORT=8001
DEBUG=False
```

**Test it manually first:**

```bash
source .venv/bin/activate
uvicorn api_server:app --host 127.0.0.1 --port 8001
# Hit Ctrl+C when confirmed working
```

Verify:
```bash
curl http://127.0.0.1:8001/
# Should return: {"message":"Malnutrition Assessment API is running", ...}
```

Deactivate when done testing:
```bash
deactivate
```

---

## 15. Supervisor: Managing Both Python APIs

Supervisor keeps both Python FastAPI processes alive and restarts them if they crash.

### Install Supervisor

```bash
apt install -y supervisor
systemctl enable supervisor
systemctl start supervisor
```

### Create Supervisor Config for LLM API

```bash
nano /etc/supervisor/conf.d/shares-llm.conf
```

Paste:

```ini
[program:shares-llm]
command=/home/shares-app/htdocs/shares-app.site/capstone_system/LLM/.venv/bin/uvicorn fastapi_app:app --host 127.0.0.1 --port 8002 --workers 1
directory=/home/shares-app/htdocs/shares-app.site/capstone_system/LLM
user=www-data
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
startsecs=10
startretries=5
redirect_stderr=true
stdout_logfile=/var/log/supervisor/shares-llm.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
environment=HOME="/home/shares-app",USER="www-data"
```

### Create Supervisor Config for RandomForest API

```bash
nano /etc/supervisor/conf.d/shares-randomforest.conf
```

Paste:

```ini
[program:shares-randomforest]
command=/home/shares-app/htdocs/shares-app.site/capstone_system/RandomForest/.venv/bin/uvicorn api_server:app --host 127.0.0.1 --port 8001 --workers 1
directory=/home/shares-app/htdocs/shares-app.site/capstone_system/RandomForest
user=www-data
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
startsecs=10
startretries=5
redirect_stderr=true
stdout_logfile=/var/log/supervisor/shares-randomforest.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
environment=HOME="/home/shares-app",USER="www-data"
```

### Reload and Start

```bash
# Reload supervisor configuration
supervisorctl reread
supervisorctl update

# Start both services
supervisorctl start shares-llm
supervisorctl start shares-randomforest

# Check status
supervisorctl status
```

Expected output:
```
shares-llm                       RUNNING   pid 12345, uptime 0:00:10
shares-randomforest              RUNNING   pid 12346, uptime 0:00:09
```

### Common Supervisor Commands

```bash
supervisorctl status                        # view all process statuses
supervisorctl restart shares-llm            # restart LLM API
supervisorctl restart shares-randomforest   # restart RF API
supervisorctl stop shares-llm              # stop LLM API
supervisorctl stop shares-randomforest     # stop RF API
supervisorctl tail -f shares-llm           # live log for LLM
supervisorctl tail -f shares-randomforest  # live log for RF
```

---

## 16. Laravel Queue Worker via Supervisor

```bash
nano /etc/supervisor/conf.d/shares-queue.conf
```

Paste:

```ini
[program:shares-queue]
command=php /home/shares-app/htdocs/shares-app.site/capstone_system/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/home/shares-app/htdocs/shares-app.site/capstone_system
user=www-data
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=1
startsecs=10
stopwaitsecs=3600
redirect_stderr=true
stdout_logfile=/var/log/supervisor/shares-queue.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start shares-queue
supervisorctl status shares-queue
```

---

## 17. GitHub → VPS Update Workflow

Every time you push changes to GitHub, SSH into the VPS and run the **deploy script**.

### One-liner update

```bash
cd /home/shares-app/htdocs/shares-app.site

# 1. Pull latest code
git pull origin main

# 2. Install/update Composer packages
cd capstone_system
composer install --optimize-autoloader --no-dev

# 3. Run new migrations
php artisan migrate --force

# 4. Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Rebuild frontend (only if JS/CSS changed)
npm ci --omit=dev
npm run build

# 6. Fix permissions
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache

# 7. Restart Python APIs (if Python files changed)
supervisorctl restart shares-llm
supervisorctl restart shares-randomforest

# 8. Restart queue worker
supervisorctl restart shares-queue
```

### Create a Deploy Script (optional but recommended)

```bash
nano /home/shares-app/deploy.sh
```

Paste:

```bash
#!/bin/bash
set -e

SITE_DIR="/home/shares-app/htdocs/shares-app.site"
APP_DIR="$SITE_DIR/capstone_system"

echo "==> Pulling latest code from GitHub..."
cd "$SITE_DIR"
git pull origin main

echo "==> Installing Composer dependencies..."
cd "$APP_DIR"
composer install --optimize-autoloader --no-dev

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Clearing and rebuilding Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Rebuilding frontend assets..."
npm ci --omit=dev
npm run build

echo "==> Fixing file permissions..."
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache

echo "==> Restarting Supervisor processes..."
supervisorctl restart shares-llm
supervisorctl restart shares-randomforest
supervisorctl restart shares-queue

echo ""
echo "✅ Deploy complete! All services restarted."
supervisorctl status
```

Make it executable:

```bash
chmod +x /home/shares-app/deploy.sh
```

Run a deploy:

```bash
bash /home/shares-app/deploy.sh
```

---

## 18. Health Checks & Verification

### Laravel

```bash
curl -I https://shares-app.site
# Should return HTTP/2 200

php artisan about
# Shows env, cache driver, queue driver
```

### LLM API

```bash
curl http://127.0.0.1:8002/
# {"message":"Meal Planning API is running","status":"healthy","version":"1.0"}
```

### RandomForest API

```bash
curl http://127.0.0.1:8001/
# {"message":"Malnutrition Assessment API is running","status":"healthy",...}
```

### Supervisor Status

```bash
supervisorctl status
# All three processes should show RUNNING
```

### Laravel Logs

```bash
tail -n 50 /home/shares-app/htdocs/shares-app.site/capstone_system/storage/logs/laravel.log
```

### Supervisor Logs

```bash
tail -f /var/log/supervisor/shares-llm.log
tail -f /var/log/supervisor/shares-randomforest.log
tail -f /var/log/supervisor/shares-queue.log
```

---

## 19. Common Errors & Fixes

### ❌ ERR_TOO_MANY_REDIRECTS

- Set Cloudflare SSL mode to **Full (Strict)**
- Verify `TrustProxies.php` has `$proxies = '*'`

### ❌ 500 Server Error on login

```bash
tail -n 100 capstone_system/storage/logs/laravel.log
```
- Check `APP_KEY` is set: `php artisan key:generate`
- Check `APP_DEBUG=false` → temporarily set to `true` to see the real error, then flip back

### ❌ Python API BACKOFF (not starting)

```bash
supervisorctl tail shares-llm
# OR view the full log:
cat /var/log/supervisor/shares-llm.log
```
- Usually a missing `.env` or Python package install error
- Re-run: `source .venv/bin/activate && pip install -r requirements.txt`

### ❌ "Unable to connect to meal plan service"

```bash
curl http://127.0.0.1:8002/
```
- If no response → LLM service isn't actually running despite `supervisorctl` saying RUNNING
- Check log: `supervisorctl tail shares-llm`
- Confirm port: `ss -tlnp | grep 8002`

### ❌ Storage permission denied

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

### ❌ git pull fails (permission denied)

```bash
# Make sure git is run as root or the app user, not www-data
sudo -u root git -C /home/shares-app/htdocs/shares-app.site pull origin main
```

### ❌ Queue worker BACKOFF

```bash
supervisorctl tail shares-queue
php artisan queue:work --once   # test one job manually
```
- Make sure `QUEUE_CONNECTION=database` in `.env`
- Run `php artisan migrate --force` (jobs table may be missing)

---

*Generated for SHAReS Capstone System — Hostinger KVM 2 Deployment*
