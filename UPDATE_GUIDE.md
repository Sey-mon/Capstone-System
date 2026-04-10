# SHAReS System — Update Guide
### Day-to-Day Update Workflow · Local → GitHub → VPS

> Use this guide every time you push new changes and need to apply them to the live VPS.  
> This assumes the VPS is already deployed and running.

---

## The Workflow

```
Your Local Machine  →  git push  →  GitHub  →  git pull (on VPS)
```

---

## Step 1 — Push Changes from Local Machine

On your **local machine** (Windows), commit and push as usual:

```bash
git add .
git commit -m "your commit message"
git push origin main
```

---

## Step 2 — SSH into VPS

```bash
ssh root@<your-vps-ip>
```

---

## Step 3 — Pull Latest Changes

```bash
cd /home/shares-app/htdocs/shares-app.site

git pull origin main
```

> ✅ Files update **in place** — no new folders will be created.

---

## Step 4 — Apply Changes (pick what applies)

### 🔵 Always run after every pull

```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system

# Clear and rebuild Laravel caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 🟡 Run if you changed PHP/Composer files (`composer.json`, any `app/` files)

```bash
composer install --optimize-autoloader --no-dev
```

---

### 🟡 Run if you added new database migrations

```bash
php artisan migrate --force
```

---

### 🟡 Run if you changed JS/CSS files (`resources/`, `vite.config.js`, `package.json`)

```bash
npm ci --omit=dev
npm run build
```

---

### 🟡 Run if you changed Python files in `LLM/` or `RandomForest/`

```bash
# Restart the relevant API service
supervisorctl restart shares-llm            # if LLM/ files changed
supervisorctl restart shares-randomforest   # if RandomForest/ files changed
```

If you also changed `requirements.txt` for either service, rebuild the venv first:

**LLM API:**
```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system/LLM
source .venv/bin/activate
pip install -r requirements.txt
deactivate
supervisorctl restart shares-llm
```

**RandomForest API:**
```bash
cd /home/shares-app/htdocs/shares-app.site/capstone_system/RandomForest
source .venv/bin/activate
pip install -r requirements.txt
deactivate
supervisorctl restart shares-randomforest
```

---

### 🟡 Run if you changed queue-related jobs

```bash
supervisorctl restart shares-queue
```

---

### 🟡 Run if you changed file storage or added new public assets

```bash
chown -R www-data:www-data /home/shares-app/htdocs/shares-app.site/capstone_system
chmod -R 775 /home/shares-app/htdocs/shares-app.site/capstone_system/storage \
             /home/shares-app/htdocs/shares-app.site/capstone_system/bootstrap/cache
```

---

## Full Update (run everything at once)

Use this when you're unsure what changed or want to be safe:

```bash
cd /home/shares-app/htdocs/shares-app.site

# Pull latest code
git pull origin main

cd capstone_system

# PHP dependencies
composer install --optimize-autoloader --no-dev

# Migrations
php artisan migrate --force

# Laravel caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend assets
npm ci --omit=dev
npm run build

# Permissions
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache

# Restart all services
supervisorctl restart shares-llm
supervisorctl restart shares-randomforest
supervisorctl restart shares-queue
```

---

## Quick Status Check

After any update, verify everything is still running:

```bash
# Supervisor processes
supervisorctl status

# Test LLM API
curl http://127.0.0.1:8002/

# Test RandomForest API
curl http://127.0.0.1:8001/

# Check for Laravel errors
tail -n 30 /home/shares-app/htdocs/shares-app.site/capstone_system/storage/logs/laravel.log
```

Expected `supervisorctl status` output:
```
shares-llm                       RUNNING   pid xxxxx, uptime x:xx:xx
shares-queue                     RUNNING   pid xxxxx, uptime x:xx:xx
shares-randomforest              RUNNING   pid xxxxx, uptime x:xx:xx
```

---

## Using the Deploy Script (Optional)

If you set up `deploy.sh` during initial deployment, you can run everything in one command:

```bash
bash /home/shares-app/deploy.sh
```

---

## Quick Reference — Supervisor Commands

| Command | What it does |
|---|---|
| `supervisorctl status` | View all process statuses |
| `supervisorctl restart shares-llm` | Restart LLM API |
| `supervisorctl restart shares-randomforest` | Restart RF API |
| `supervisorctl restart shares-queue` | Restart Laravel queue |
| `supervisorctl restart all` | Restart everything |
| `supervisorctl tail -f shares-llm` | Live log — LLM API |
| `supervisorctl tail -f shares-randomforest` | Live log — RF API |
| `supervisorctl tail -f shares-queue` | Live log — queue worker |

---

## Common Issues After an Update

### ❌ Site shows old content / 500 error after pull
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

### ❌ Python API not reflecting new code changes
```bash
supervisorctl restart shares-llm
# or
supervisorctl restart shares-randomforest
```

### ❌ Migration failed
```bash
php artisan migrate:status        # check which migrations are pending
php artisan migrate --force       # rerun
```

### ❌ npm build error
```bash
rm -rf node_modules
npm ci --omit=dev
npm run build
```

### ❌ Permission denied errors
```bash
chown -R www-data:www-data /home/shares-app/htdocs/shares-app.site/capstone_system
chmod -R 775 /home/shares-app/htdocs/shares-app.site/capstone_system/storage \
             /home/shares-app/htdocs/shares-app.site/capstone_system/bootstrap/cache
```

---

*Generated for SHAReS Capstone System — Day-to-Day Update Workflow*
