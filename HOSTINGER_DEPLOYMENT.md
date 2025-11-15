# 🚀 Hostinger Deployment Guide for SHAReS System

This guide will walk you through deploying your Laravel application to Hostinger step-by-step.

## 📋 Prerequisites

Before starting, make sure you have:
- ✅ Active Hostinger hosting account
- ✅ Domain configured in Hostinger
- ✅ SSH access enabled (recommended)
- ✅ MySQL database created in hPanel
- ✅ GitHub repository set up

---

## 🔧 Step 1: Configure GitHub Actions

The workflow is already set up in `.github/workflows/publish.yml`. When you push to `main` branch, it automatically:
- Builds your Laravel app with production dependencies
- Creates optimized assets
- Pushes to `deploy-hostinger` branch

**To trigger deployment:**
```bash
git add .
git commit -m "Deploy to Hostinger"
git push origin main
```

Wait 2-3 minutes for GitHub Actions to complete. Check the "Actions" tab in your GitHub repository.

---

## 📁 Step 2: Download Built Files

After GitHub Actions completes:

1. Go to your repository on GitHub
2. Switch to the `deploy-hostinger` branch
3. Click **Code → Download ZIP**
4. Extract the ZIP file on your computer

**Alternative (using Git):**
```bash
git clone --branch deploy-hostinger https://github.com/YOUR_USERNAME/Capstone-System.git hostinger-deploy
```

---

## 📤 Step 3: Upload Files to Hostinger

### Option A: Using File Manager (Easier)

1. Login to **hPanel** → File Manager
2. Navigate to your domain folder (e.g., `domains/yourdomain.com/`)
3. **Delete existing files** in the directory (backup first if needed)
4. **Upload ALL files** from the extracted build
5. Make sure the structure looks like this:
   ```
   domains/yourdomain.com/
   ├── app/
   ├── bootstrap/
   ├── config/
   ├── database/
   ├── public_html/     ← Your web root
   │   ├── index.php
   │   ├── .htaccess
   │   ├── css/
   │   └── js/
   ├── resources/
   ├── routes/
   ├── storage/
   ├── vendor/
   ├── .env
   ├── .htaccess        ← Root redirect
   ├── artisan
   └── composer.json
   ```

### Option B: Using SSH (Faster for large files)

1. Enable SSH in hPanel → Advanced → SSH Access
2. Connect via SSH:
   ```bash
   ssh u123456789@yourdomain.com
   ```
3. Navigate to your domain folder:
   ```bash
   cd domains/yourdomain.com
   ```
4. Upload files using SFTP (FileZilla, WinSCP, or rsync)

---

## ⚙️ Step 4: Configure Environment (.env)

1. Open `.env` file in File Manager or via SSH
2. Update these critical settings:

```env
APP_NAME="SHAReS System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

⚠️ **Important**: Replace database credentials with your Hostinger MySQL details from hPanel → Databases.

---

## 🔑 Step 5: Generate Application Key

Via SSH:
```bash
cd domains/yourdomain.com
php artisan key:generate
```

**No SSH?** Use hPanel Terminal or manually generate:
1. Go to https://generate-random.org/laravel-key-generator
2. Copy the generated key
3. Replace `APP_KEY=` value in `.env`

---

## 🗄️ Step 6: Set Up Database

### Via SSH:
```bash
cd domains/yourdomain.com
php artisan migrate --force
php artisan db:seed --force
```

### No SSH? Use phpMyAdmin:
1. hPanel → Databases → phpMyAdmin
2. Import your database SQL file manually

---

## 🔒 Step 7: Set Permissions

Via SSH:
```bash
cd domains/yourdomain.com
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache
```

**File Manager Method:**
1. Right-click `storage` folder → Permissions → 775
2. Check "Apply to subdirectories"
3. Repeat for `bootstrap/cache`

---

## 🚀 Step 8: Optimize Application

Via SSH:
```bash
cd domains/yourdomain.com
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

**No SSH?** Your app will work but may be slower. Try to get SSH access.

---

## 🌐 Step 9: Configure Domain

1. Go to hPanel → Domains
2. Find your domain → **Manage**
3. Under "Document root", verify it points to: `public_html`
4. If not, change it to `public_html`
5. Save changes

---

## ✅ Step 10: Test Your Application

1. Visit your domain: `https://yourdomain.com`
2. You should see your Laravel application
3. Test login, registration, and core features

---

## 🐛 Troubleshooting

### "500 Internal Server Error"

**Solution 1**: Check `.htaccess` exists in `public_html`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Solution 2**: Check error logs in hPanel → Advanced → Error Logs

**Solution 3**: Set permissions again:
```bash
chmod -R 775 storage bootstrap/cache
```

**Solution 4**: Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### "Laravel Mix manifest not found"

```bash
cd domains/yourdomain.com
php artisan optimize:clear
```

### Assets (CSS/JS) not loading

Check if files exist in `public_html/build/` or `public_html/css/` and `public_html/js/`

Update `.env`:
```env
ASSET_URL=https://yourdomain.com
```

### Database connection error

1. Verify database credentials in `.env`
2. Check if database exists in hPanel → Databases
3. Ensure database user has all privileges
4. Try `DB_HOST=127.0.0.1` instead of `localhost`

### Permission denied errors

```bash
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

Replace `username` with your Hostinger username (check with `whoami`).

---

## 🔄 Updating Your Application

When you need to deploy changes:

1. **Make changes** in your local repository
2. **Commit and push** to main:
   ```bash
   git add .
   git commit -m "Your changes"
   git push origin main
   ```
3. **Wait for GitHub Actions** to build (2-3 minutes)
4. **Download new build** from `deploy-hostinger` branch
5. **Upload to Hostinger** (overwrite existing files)
6. **Clear cache** via SSH:
   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```

---

## 📞 Getting Help

If you're still stuck:

1. **Check Hostinger logs**: hPanel → Advanced → Error Logs
2. **Check Laravel logs**: `storage/logs/laravel.log`
3. **Enable debug temporarily**: Set `APP_DEBUG=true` in `.env` (remember to disable after)
4. **Hostinger Support**: Available 24/7 via live chat

---

## ✨ Tips for Success

- ✅ Always backup before updating
- ✅ Use SSH when possible (much faster)
- ✅ Keep `APP_DEBUG=false` in production
- ✅ Regularly check error logs
- ✅ Set up automated backups in hPanel
- ✅ Use HTTPS (enable in hPanel → SSL)
- ✅ Set up cron jobs for Laravel scheduler if needed

---

## 🎯 Next Steps

- [ ] Configure SSL certificate (Let's Encrypt in hPanel)
- [ ] Set up email (SMTP settings in `.env`)
- [ ] Configure cron jobs for scheduled tasks
- [ ] Set up automated database backups
- [ ] Configure Python ML components (if needed)

---

**Good luck with your deployment! 🎉**
