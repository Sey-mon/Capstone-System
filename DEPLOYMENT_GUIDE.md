# CloudPanel Deployment Guide - SHAReS System

Complete step-by-step guide to deploy your Laravel application with Python AI APIs on CloudPanel.

## Prerequisites

- CloudPanel server with SSH access
- Domain name pointed to your server
- Root or sudo access
- Git installed on server

## System Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Node.js 18+ and npm
- Python 3.9+ with pip
- Composer
- Supervisor (for queue workers and Python APIs)

---

## Part 1: Server Preparation

### Step 1: Connect to Your Server

```bash
ssh root@your-server-ip
```

### Step 2: Update System Packages

```bash
apt update && apt upgrade -y
```

### Step 3: Install Required Software

```bash
# Install Python and pip
apt install python3 python3-pip python3-venv -y

# Install Supervisor for background processes
apt install supervisor -y

# Install Node.js (if not already installed)
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Verify installations
php -v
python3 --version
node -v
npm -v
composer --version
```

---

## Part 2: Create Site in CloudPanel

### Step 4: Create New Site

1. Login to CloudPanel dashboard (https://your-server-ip:8443)
2. Go to **Sites** → **Add Site**
3. Fill in the details:
   - **Site Type**: PHP
   - **PHP Version**: 8.2 or higher
   - **Domain Name**: shares-app.site
   - **Site User**: shares-app
4. Click **Create**

### Step 5: Note Important Paths

After creating the site, note these paths:
- **Site Root**: `/home/shares-app/htdocs/shares-app.site`
- **Public Directory**: `/home/shares-app/htdocs/shares-app.site/public`

---

## Part 3: Deploy Laravel Application

### Step 6: Navigate to Site Directory

```bash
cd /home/shares-app/htdocs/shares-app.site
```

### Step 7: Clone Repository

```bash
# Remove default files
rm -rf *

# Clone your repository
git clone https://github.com/Sey-mon/Capstone-System.git .

# Move Laravel files to root
mv capstone_system/* .
mv capstone_system/.* . 2>/dev/null || true
rmdir capstone_system
```

### Step 8: Set Correct Permissions

```bash
# Set proper permissions (as root)
chmod -R 755 /home/shares-app/htdocs/shares-app.site

# Change ownership to site user
chown -R shares-app:shares-app /home/shares-app/htdocs/shares-app.site
```

### Step 9: Install PHP Dependencies

```bash
# Switch to site user
su - shares-app

# Navigate to site
cd ~/htdocs/shares-app.site

# Install Composer dependencies
composer install --optimize-autoloader --no-dev
```

### Step 10: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

**Update these critical values in `.env`:**

```env
APP_NAME="SHAReS System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shares-app.site

# Database Configuration (get from CloudPanel)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Mail Configuration (Brevo SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@shares-app.site
MAIL_FROM_NAME="${APP_NAME}"

# API URLs (will be localhost on same server)
LLM_API_URL=http://127.0.0.1:8002
LLM_API_KEY=your_secure_api_key_here
RF_API_URL=http://127.0.0.1:8001
RF_API_KEY=your_secure_api_key_here

# Session & Queue
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Save and exit (Ctrl+X, Y, Enter)

### Step 11: Create Database

**Option A: Using CloudPanel Dashboard**
1. Go to **Databases** → **Add Database**
2. Create database, user, and password
3. Update `.env` with these credentials

**Option B: Using Command Line**

```bash
mysql -u root -p

CREATE DATABASE capstone_shares CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'capstone_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON capstone_shares.* TO 'capstone_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 12: Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed essential data (in order, excluding test patient/assessment data)
php artisan db:seed --class=RolesTableSeeder --force
php artisan db:seed --class=BarangaysTableSeeder --force
php artisan db:seed --class=ItemCategoriesTableSeeder --force
php artisan db:seed --class=UsersTableSeeder --force
php artisan db:seed --class=InventoryItemsTableSeeder --force

# Note: PatientTableSeeder and AssessmentsTableSeeder are skipped for production
# These contain test data only
```

### Step 13: Install and Build Frontend Assets

```bash
# Install npm packages
npm install

# Build production assets
npm run build

# Clear and optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 14: Create Storage Symlink

```bash
php artisan storage:link
```

---

## Part 4: Configure Document Root in CloudPanel

### Step 15: Update Document Root

1. In CloudPanel, go to your site
2. Navigate to **Vhost** or **Settings**
3. Change document root to point to `/public` directory:
   - From: `/home/shares-app/htdocs/shares-app.site`
   - To: `/home/shares-app/htdocs/shares-app.site/public`
4. Save and reload Nginx/Apache

**Or manually edit vhost config:**

```bash
# Edit Nginx config
nano /etc/nginx/sites-enabled/shares-app.site.conf

# Find root directive and change to:
root /home/shares-app/htdocs/shares-app.site/public;

# Test Nginx config
nginx -t

# Reload Nginx
systemctl reload nginx
```

---

## Part 5: Deploy Python APIs

### Step 16: Setup LLM API (Port 8002)

```bash
# Navigate to LLM directory
cd /home/shares-app/htdocs/shares-app.site/LLM

# Create virtual environment
python3 -m venv venv

# Activate virtual environment
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Create .env file
nano .env
```

**LLM API `.env` content:**

```env
GROQ_API_KEY=your_groq_api_key_here
DB_HOST=localhost
DB_USER=your_database_user
DB_PASSWORD=your_database_password
DB_NAME=capstone_shares
API_KEY=your_secure_api_key_here
PORT=8002
```

Save and exit.

```bash
# Test the API
uvicorn fastapi_app:app --host 127.0.0.1 --port 8002

# If it works, press Ctrl+C and continue
deactivate
```

### Step 17: Setup RandomForest API (Port 8001)

```bash
# Navigate to RandomForest directory
cd /home/shares-app/htdocs/shares-app.site/RandomForest

# Create virtual environment
python3 -m venv venv

# Activate virtual environment
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Create .env file if needed
nano .env
```

**RandomForest API `.env` content:**

```env
API_KEY=your_secure_api_key_here
PORT=8001
```

Save and exit.

```bash
# Test the API
uvicorn api_server:app --host 127.0.0.1 --port 8001

# If it works, press Ctrl+C and continue
deactivate
```

---

## Part 6: Setup Background Services with Supervisor

### Step 18: Create Supervisor Configurations

**Exit from site user back to root:**

```bash
exit
```

**Create Laravel Queue Worker Config:**

```bash
nano /etc/supervisor/conf.d/laravel-queue-worker.conf
```

**Content:**

```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/shares-app/htdocs/shares-app.site/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=shares-app
numprocs=2
redirect_stderr=true
stdout_logfile=/home/shares-app/htdocs/shares-app.site/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**Create LLM API Service Config:**

```bash
nano /etc/supervisor/conf.d/llm-api.conf
```

**Content:**

```ini
[program:llm-api]
process_name=%(program_name)s
command=/home/shares-app/htdocs/shares-app.site/LLM/venv/bin/uvicorn fastapi_app:app --host 127.0.0.1 --port 8002
directory=/home/shares-app/htdocs/shares-app.site/LLM
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=shares-app
redirect_stderr=true
stdout_logfile=/home/shares-app/htdocs/shares-app.site/storage/logs/llm-api.log
stderr_logfile=/home/shares-app/htdocs/shares-app.site/storage/logs/llm-api-error.log
```

**Create RandomForest API Service Config:**

```bash
nano /etc/supervisor/conf.d/rf-api.conf
```

**Content:**

```ini
[program:rf-api]
process_name=%(program_name)s
command=/home/shares-app/htdocs/shares-app.site/RandomForest/venv/bin/uvicorn api_server:app --host 127.0.0.1 --port 8001
directory=/home/shares-app/htdocs/shares-app.site/RandomForest
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=shares-app
redirect_stderr=true
stdout_logfile=/home/shares-app/htdocs/shares-app.site/storage/logs/rf-api.log
stderr_logfile=/home/shares-app/htdocs/shares-app.site/storage/logs/rf-api-error.log
```

### Step 19: Start All Services

```bash
# Reload Supervisor configuration
supervisorctl reread
supervisorctl update

# Start all services
supervisorctl start laravel-queue-worker:*
supervisorctl start llm-api
supervisorctl start rf-api

# Check status
supervisorctl status
```

**Expected output:**

```
laravel-queue-worker:laravel-queue-worker_00   RUNNING
laravel-queue-worker:laravel-queue-worker_01   RUNNING
llm-api                                         RUNNING
rf-api                                          RUNNING
```

---

## Part 7: SSL Certificate (HTTPS)

### Step 20: Enable SSL in CloudPanel

1. In CloudPanel, go to your site
2. Navigate to **SSL/TLS**
3. Click **New Let's Encrypt Certificate**
4. Select your domain
5. Click **Create**

CloudPanel will automatically obtain and configure SSL.

---

## Part 8: Final Optimizations

### Step 21: Configure PHP Settings

In CloudPanel:
1. Go to your site → **PHP Settings**
2. Adjust these values:
   - `memory_limit`: 256M or higher
   - `upload_max_filesize`: 20M
   - `post_max_size`: 20M
   - `max_execution_time`: 300

### Step 22: Setup Cron Jobs

```bash
# Edit crontab for site user
crontab -e -u shares-app

# Add Laravel scheduler
* * * * * cd /home/shares-app/htdocs/shares-app.site && php artisan schedule:run >> /dev/null 2>&1
```

### Step 23: Final Permission Check

```bash
cd /home/shares-app/htdocs/shares-app.site

# Ensure proper ownership
chown -R shares-app:shares-app .

# Storage and cache permissions
chmod -R 775 storage bootstrap/cache

# Public uploads
chmod -R 775 public/uploads
```

---

## Part 9: Testing & Verification

### Step 24: Test the Application

1. **Test Website:**
   - Visit: `https://shares-app.site`
   - You should see the login/home page

2. **Test APIs:**

```bash
# Test LLM API
curl http://127.0.0.1:8002/health

# Test RandomForest API
curl http://127.0.0.1:8001/health
```

3. **Check Logs:**

```bash
# Laravel logs
tail -f /home/shares-app/htdocs/shares-app.site/storage/logs/laravel.log

# Queue worker logs
tail -f /home/shares-app/htdocs/shares-app.site/storage/logs/queue-worker.log

# LLM API logs
tail -f /home/shares-app/htdocs/shares-app.site/storage/logs/llm-api.log

# RF API logs
tail -f /home/shares-app/htdocs/shares-app.site/storage/logs/rf-api.log

# Nginx error logs
tail -f /var/log/nginx/error.log
```

4. **Check Service Status:**

```bash
supervisorctl status
```

---

## Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Check Nginx logs: `tail -f /var/log/nginx/error.log`
- Verify permissions: `chmod -R 775 storage bootstrap/cache`
- Clear cache: `php artisan cache:clear && php artisan config:clear`

**2. Database Connection Failed**
- Verify credentials in `.env`
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

**3. Python APIs Not Working**
- Check if services are running: `supervisorctl status`
- Check logs in `storage/logs/`
- Restart services: `supervisorctl restart llm-api rf-api`

**4. Assets Not Loading (CSS/JS)**
- Rebuild assets: `npm run build`
- Clear Laravel cache: `php artisan optimize:clear`
- Check document root points to `/public`

**5. Queue Jobs Not Processing**
- Check worker status: `supervisorctl status laravel-queue-worker:*`
- Restart workers: `supervisorctl restart laravel-queue-worker:*`
- Check logs: `tail -f storage/logs/queue-worker.log`

---

## Maintenance Commands

### Update Application

```bash
cd /home/shares-app/htdocs/shares-app.site

# Pull latest changes
git pull origin main  # or your branch name

# Install/update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
supervisorctl restart laravel-queue-worker:* llm-api rf-api
```

### Restart All Services

```bash
supervisorctl restart all
```

### View All Logs

```bash
# One-line command to watch all logs
tail -f storage/logs/*.log
```

---

## Security Checklist

- [ ] `.env` file is not in git repository
- [ ] `APP_DEBUG=false` in production
- [ ] Database uses strong password
- [ ] API keys are secure and unique
- [ ] SSL certificate is active (HTTPS)
- [ ] File permissions are correct (755 for directories, 644 for files)
- [ ] Storage and cache directories are writable (775)
- [ ] Firewall configured (only ports 80, 443, 22, 8443 open)
- [ ] Regular backups configured
- [ ] Supervisor services auto-restart on failure

---

## Post-Deployment

### Create Admin User

```bash
php artisan tinker

# Create admin user
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@shares-app.site';
$user->password = Hash::make('your_secure_password');
$user->save();

# Assign admin role (if using roles)
$user->assignRole('admin');
```

---

## Backup Strategy

### Database Backup

```bash
# Create backup script
nano /home/shares-app/backup-database.sh
```

**Script content:**

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/shares-app/backups"
mkdir -p $BACKUP_DIR

mysqldump -u your_db_user -p'your_db_password' capstone_shares > $BACKUP_DIR/db_backup_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

```bash
chmod +x /home/shares-app/backup-database.sh

# Add to crontab (daily at 2 AM)
crontab -e -u shares-app
0 2 * * * /home/shares-app/backup-database.sh
```

---

## Support & Resources

- Laravel Documentation: https://laravel.com/docs
- CloudPanel Documentation: https://www.cloudpanel.io/docs
- FastAPI Documentation: https://fastapi.tiangolo.com
- GitHub Repository: https://github.com/Sey-mon/Capstone-System

---

**Deployment Complete! 🚀**

Your SHAReS System should now be live at `https://shares-app.site`
