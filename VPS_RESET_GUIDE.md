# SHAReS System — VPS Reset Guide
### Full Reset Procedure · Hostinger KVM 2 · CloudPanel

> **Use this guide** when you need to wipe the existing VPS deployment and start completely fresh — same server, new clean clone.  
> **⚠️ This will delete all application files, environment configs, and Python virtualenvs.** It will NOT touch your database unless you explicitly follow Section 4.

---

## When to Use This

- Previous deployment has too many broken configs/errors to salvage
- You want to re-clone from a fresh state
- Supervisor configs are mangled or services refuse to start
- You need to realign paths after a structural repo change

---

## Table of Contents
1. [Stop All Running Services](#1-stop-all-running-services)
2. [Remove Old Application Files](#2-remove-old-application-files)
3. [(Optional) Wipe and Recreate the Database](#3-optional-wipe-and-recreate-the-database)
4. [Remove Old Supervisor Configs](#4-remove-old-supervisor-configs)
5. [Re-clone the Repository](#5-re-clone-the-repository)
6. [Update CloudPanel Document Root](#6-update-cloudpanel-document-root)
7. [Laravel Fresh Setup](#7-laravel-fresh-setup)
8. [Rebuild LLM Python Environment](#8-rebuild-llm-python-environment)
9. [Rebuild RandomForest Python Environment](#9-rebuild-randomforest-python-environment)
10. [Recreate Supervisor Configs](#10-recreate-supervisor-configs)
11. [Start Everything & Verify](#11-start-everything--verify)

---

## 1. Stop All Running Services

SSH into your VPS first:

```bash
ssh root@<your-vps-ip>
```

Stop all Supervisor-managed processes:

```bash
supervisorctl stop all
supervisorctl status
# All processes should show STOPPED
```

If Supervisor itself is causing problems, stop the daemon:

```bash
systemctl stop supervisor
```

---

## 2. Remove Old Application Files

```bash
# Navigate to the parent directory
cd /home/shares-app/htdocs/

# Delete everything in the site directory (keeps the directory itself)
rm -rf shares-app.site

# Recreate the empty directory
mkdir -p shares-app.site

# Confirm it's empty
ls -la shares-app.site/
```

> **Note:** If CloudPanel owns or recreated `shares-app.site` automatically, that's fine — just ensure it's empty before cloning.

---

## 3. (Optional) Wipe and Recreate the Database

> ⚠️ **SKIP THIS SECTION** if you want to preserve your existing data.  
> Only do this if you want a completely clean database slate.

Log into MariaDB/MySQL as root:

```bash
mysql -u root -p
```

Inside the MySQL prompt:

```sql
-- Drop existing database
DROP DATABASE IF EXISTS capstone;

-- Recreate it fresh
CREATE DATABASE capstone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Confirm the user still has access (should already exist from CloudPanel)
GRANT ALL PRIVILEGES ON capstone.* TO 'capstone_user'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

Verify access:

```bash
mysql -u capstone_user -p capstone
# Should connect successfully, then EXIT;
```

---

## 4. Remove Old Supervisor Configs

Your existing VPS has these config file names (confirmed):

```bash
# Remove the OLD supervisor configs (actual filenames on this VPS)
rm -f /etc/supervisor/conf.d/llm-api.conf
rm -f /etc/supervisor/conf.d/rf-api.conf
rm -f /etc/supervisor/conf.d/laravel-queue-worker.conf

# Remove any old log files (both old and new naming conventions)
rm -f /var/log/supervisor/llm-api.log
rm -f /var/log/supervisor/rf-api.log
rm -f /var/log/supervisor/laravel-queue-worker.log
rm -f /var/log/supervisor/shares-llm.log
rm -f /var/log/supervisor/shares-randomforest.log
rm -f /var/log/supervisor/shares-queue.log

# Confirm removal — should only show default supervisor files
ls /etc/supervisor/conf.d/
```

---

## 5. Re-clone the Repository

```bash
cd /home/shares-app/htdocs/

# Clone fresh — directly into shares-app.site/
git clone https://<your-github-pat>@github.com/<your-username>/Capstone-System.git shares-app.site

# Verify the structure
ls shares-app.site/
# You should see: capstone_system/, DEPLOYMENT_GUIDE.md, README.md, etc.
```

---

## 6. Update CloudPanel Document Root

> Only needed if you haven't done this before, or if CloudPanel reset it.

1. Log into CloudPanel: `https://<your-vps-ip>:8443`
2. Sites → `shares-app.site` → **Settings**
3. Set **Document Root** to:
   ```
   /home/shares-app/htdocs/shares-app.site/capstone_system/public
   ```
4. Save → Restart Nginx (either via CloudPanel UI or `systemctl reload nginx`)

---

## 7. Laravel Fresh Setup

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system

# Install Composer packages
composer install --optimize-autoloader --no-dev

# Copy and configure .env
cp .env.production .env
nano .env
```

Set these values in `.env` (see full reference in DEPLOYMENT_GUIDE.md §7):

```dotenv
APP_KEY=                     # blank — generate next
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shares-app.site

DB_HOST=127.0.0.1
DB_DATABASE=capstone
DB_USERNAME=capstone_user
DB_PASSWORD=<your-db-password>

LLM_API_URL=http://127.0.0.1:8002
RANDOM_FOREST_API_URL=http://127.0.0.1:8001
RANDOM_FOREST_API_KEY=malnutrition-api-key-2025
```

```bash
# Generate fresh app key
   php artisan key:generate

# Run migrations
php artisan migrate --force

# if the site already has a db
# php artisan migrate:install
# php artisan tinker
# $batch = 1;
# $files = glob(database_path('migrations/*.php'));
# foreach ($files as $file) {
#     $name = basename($file, '.php');
#     DB::table('migrations')->insertOrIgnore(['migration' => $name, 'batch' => $batch]);
# }
# echo count($files) . " migrations marked as completed.\n";
# exit;
# php artisan migrate:status


# (Optional) Seed — ONLY if database is empty/wiped
# php artisan db:seed --force

# Build frontend assets
npm ci --omit=dev
npm run build

# Fix permissions
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache

# Create storage symlink
php artisan storage:link

# Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 8. Rebuild LLM Python Environment

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system/LLM

# If an old .venv exists, delete it
rm -rf .venv

# Create fresh virtualenv
python3.11 -m venv .venv

# Activate and install
source .venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt


# Create the .env for the LLM service
nano .env
```

Paste into `LLM/.env`:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=capstone
DB_USER=capstone_user
DB_PASSWORD=<your-db-password>

GROQ_API_KEY=<your-groq-api-key>
```

Test manually:

```bash
uvicorn fastapi_app:app --host 127.0.0.1 --port 8002
# Verify: curl http://127.0.0.1:8002/
# Expected: {"message":"Meal Planning API is running",...}
# Hit Ctrl+C when confirmed
```

```bash
deactivate
```

---

## 9. Rebuild RandomForest Python Environment

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system/RandomForest

# If an old .venv exists, delete it
rm -rf .venv

# Create fresh virtualenv
python3.11 -m venv .venv

# Activate and install
source .venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt

# Create the .env for the RF service
nano .env
```

Paste into `RandomForest/.env`:

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

Test manually:

```bash
uvicorn api_server:app --host 127.0.0.1 --port 8001
# Verify: curl http://127.0.0.1:8001/
# Expected: {"message":"Malnutrition Assessment API is running",...}
# Hit Ctrl+C when confirmed
```

```bash
deactivate
```

---

## 10. Recreate Supervisor Configs

# LLM API

nano /etc/supervisor/conf.d/shares-llm.conf

---

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
---
<!-- mkdir -p /var/cache/huggingface
HF_HOME=/var/cache/huggingface \
  /home/shares-app/htdocs/shares-app.site/capstone_system/LLM/.venv/bin/python \
  -c "from sentence_transformers import SentenceTransformer; print('Downloading...'); SentenceTransformer('sentence-transformers/all-MiniLM-L6-v2'); print('Done!')"
chown -R www-data:www-data /var/cache/huggingface
chmod -R 755 /var/cache/huggingface
nano /etc/supervisor/conf.d/shares-llm.conf
environment=HOME="/var/cache",USER="www-data",HF_HOME="/var/cache/huggingface",TRANSFORMERS_CACHE="/var/cache/huggingface/hub" -->

---

# RandomForest API
nano /etc/supervisor/conf.d/shares-randomforest.conf


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
---


# Laravel Queue Worker
nano /etc/supervisor/conf.d/shares-queue.conf

```
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

---

# Remove the broken configs
rm -f /etc/supervisor/conf.d/shares-llm.conf
rm -f /etc/supervisor/conf.d/shares-randomforest.conf
rm -f /etc/supervisor/conf.d/shares-queue.conf

---

## 11. Start Everything & Verify

```bash
# Make sure Supervisor daemon is running
systemctl start supervisor
systemctl enable supervisor

# Load new configs
supervisorctl reread
supervisorctl update

# Start all services
supervisorctl start all

# Check status — all three should be RUNNING
supervisorctl status
```

Expected:
```
shares-llm                       RUNNING   pid xxxxx, uptime 0:00:12
shares-queue                     RUNNING   pid xxxxx, uptime 0:00:12
shares-randomforest              RUNNING   pid xxxxx, uptime 0:00:11
```

### Final Verification Checks

```bash
# Test Laravel is responding
curl -I https://shares-app.site
# → HTTP/2 200

# Test LLM API
curl http://127.0.0.1:8002/
# → {"message":"Meal Planning API is running","status":"healthy",...}

# Test RandomForest API
curl http://127.0.0.1:8001/
# → {"message":"Malnutrition Assessment API is running","status":"healthy",...}

# Check Laravel logs for errors
tail -n 50 /home/shares-app/htdocs/shares-app.site/capstone_system/storage/logs/laravel.log

# Check supervisor logs
tail -f /var/log/supervisor/shares-llm.log
tail -f /var/log/supervisor/shares-randomforest.log
```

---

## Quick Reference — Supervisor Commands

| Command | What it does |
|---|---|
| `supervisorctl status` | View status of all processes |
| `supervisorctl stop all` | Stop everything |
| `supervisorctl start all` | Start everything |
| `supervisorctl restart shares-llm` | Restart LLM API |
| `supervisorctl restart shares-randomforest` | Restart RF API |
| `supervisorctl restart shares-queue` | Restart Laravel queue |
| `supervisorctl tail -f shares-llm` | Live log — LLM API |
| `supervisorctl tail -f shares-randomforest` | Live log — RF API |
| `supervisorctl reread && supervisorctl update` | Reload config changes |

---

*Generated for SHAReS Capstone System — VPS Reset Procedure*
