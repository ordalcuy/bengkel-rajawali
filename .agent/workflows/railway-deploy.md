---
description: Deploy aplikasi Laravel ke Railway (Recommended)
---

# Deploy Laravel ke Railway

Railway adalah **alternatif terbaik** untuk deploy Laravel, dengan MySQL gratis dan setup yang lebih mudah dibanding Render.

## ✨ Keunggulan Railway

- ✅ MySQL Database **Gratis** (5GB storage)
- ✅ Setup lebih mudah (auto-detect Laravel)
- ✅ Logs lebih informatif
- ✅ Environment variables management lebih baik
- ✅ Free tier: $5 credit/bulan (cukup untuk 1-2 project)
- ✅ No sleep/cold start issues

---

## 🚀 Langkah Deploy ke Railway

### 1. Persiapan Repository

Pastikan code sudah di GitHub:

// turbo
```bash
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

> **Note**: Railway bisa deploy dari branch `main` atau `develop`, pilih sesuai kebutuhan.

### 2. Sign Up Railway

1. Buka https://railway.app
2. Klik **"Start a New Project"**
3. Login dengan **GitHub** (recommended untuk auto-deploy)
4. Verify email jika diminta

### 3. Create New Project

1. Di Railway Dashboard, klik **"+ New Project"**
2. Pilih **"Deploy from GitHub repo"**
3. Connect GitHub account jika belum
4. Pilih repository: `arifhidayat25/laravel-bengkel`
5. Pilih branch: `develop` atau `main`

### 4. Add MySQL Database

Railway akan auto-detect Laravel, tapi kita perlu add database:

1. Di project yang sama, klik **"+ New"** → **"Database"** → **"Add MySQL"**
2. Railway akan provision MySQL database (~30 detik)
3. Database credentials akan auto-generate

### 5. Configure Environment Variables

Klik pada **Laravel service** (bukan database) → **Variables** tab:

#### Copy Variables dari MySQL Database

Railway akan auto-create variable `DATABASE_URL`, tapi Laravel butuh format individual:

1. Klik **MySQL database service** → **Variables** tab
2. Copy nilai dari:
   - `MYSQL_HOST` (atau `MYSQLHOST`)
   - `MYSQL_PORT` (atau `MYSQLPORT`) 
   - `MYSQL_DATABASE` (atau `MYSQLDATABASE`)
   - `MYSQL_USER` (atau `MYSQLUSER`)
   - `MYSQL_PASSWORD` (atau `MYSQLPASSWORD`)

#### Set Laravel Environment Variables

Kembali ke **Laravel service** → **Variables** → **Raw Editor**, paste:

```bash
# Application
APP_NAME=Laravel Bengkel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

# Database (dari MySQL service)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Filament
FILAMENT_PATH=admin

# Bengkel Settings
BENGKEL_NAMA=Bengkel Rajawali Motor
BENGKEL_ALAMAT=Jl. Mertojoyo Selatan No. 4A, Merjosari, Lowokwaru, Kota Malang
BENGKEL_TELEPON=085645523234

# WhatsApp (optional)
# FONNTE_API_TOKEN=
```

> **Railway Magic**: Gunakan `${{MySQL.VARIABLE_NAME}}` untuk reference variables dari service lain!

### 6. Generate APP_KEY

Setelah deploy pertama kali:

1. Buka **Laravel service** → **Deployments** tab
2. Klik deployment terbaru → **View Logs**
3. Cari error terkait `APP_KEY`
4. Buka **Variables** tab
5. Generate key:
   - **Option A**: Via Railway CLI (jika installed)
   - **Option B**: Generate lokal:
     ```bash
     php artisan key:generate --show
     ```
   - Copy output (format: `base64:...`)
   - Paste ke variable `APP_KEY`

6. Klik **Save** (auto-redeploy)

### 7. Set Custom Start Command (Optional)

Railway auto-detect Laravel dan set start command, tapi untuk ensure migrations run:

1. **Settings** tab → **Deploy** section
2. **Custom Start Command**: 
   ```bash
   php artisan migrate --force && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=$PORT
   ```

> **Note**: Railway auto-inject `$PORT` variable

### 8. Configure Public Domain

1. **Settings** tab → **Networking** section
2. Klik **Generate Domain**
3. Railway akan generate domain: `your-app.up.railway.app`
4. Copy domain URL
5. Update `APP_URL` di **Variables**:
   ```
   APP_URL=https://your-app.up.railway.app
   ASSET_URL=https://your-app.up.railway.app
   ```

### 9. Enable Public Access

Pastikan service **Public**:

1. **Settings** tab → **Networking**
2. Pastikan **Public Networking** enabled
3. Lihat public URL yang di-generate

---

## ✅ Verifikasi Deployment

### 1. Check Build Logs

**Deployments** tab → Latest deployment → **View Logs**

Cari pesan sukses:
```
✅ Building...
✅ npm run build
✅ composer install
✅ Starting server on port 3000
```

### 2. Check Deploy Logs

Cari pesan:
```
✅ Running migrations...
✅ Server running on 0.0.0.0:xxxx
```

### 3. Test Application

1. **Homepage**: `https://your-app.up.railway.app`
2. **Admin Panel**: `https://your-app.up.railway.app/admin`
3. **Test CRUD**: Login dan coba buat data

---

## 🔧 Troubleshooting

### Build Gagal

**Symptoms**: Build error di logs

**Solutions**:
1. Check `composer.json` dan `package.json` valid
2. Pastikan `composer.lock` dan `package-lock.json` ter-commit
3. Cek Dockerfile jika ada custom build

### Migration Error

**Symptoms**: 
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solutions**:
1. Pastikan MySQL service running (check health di dashboard)
2. Verify database variables reference dengan `${{MySQL.VARIABLE}}`
3. Check network connectivity antara services

### 404 Not Found

**Symptoms**: Homepage returns 404

**Solutions**:
1. Pastikan start command menggunakan `php artisan serve`
2. Check Apache/Nginx config jika ada
3. Verify document root di `public/`

### Session/Cache Issues

**Solutions**:
```bash
# Via Custom Start Command, tambahkan:
php artisan config:clear && php artisan cache:clear && php artisan serve...
```

---

## 🎯 Post-Deployment

### 1. Setup Custom Domain (Optional)

1. **Settings** → **Networking** → **Custom Domain**
2. Add your domain (e.g., `bengkel.yourdomain.com`)
3. Update DNS records (Railway akan kasih instruksi):
   - **CNAME**: point ke Railway domain
4. Update `APP_URL` environment variable

### 2. Enable Automatic Deployments

Railway auto-deploy saat push ke GitHub:
1. **Settings** → **Deploy**
2. Pastikan **Auto-deploy** enabled
3. Set branch: `main` atau `develop`

### 3. Database Backup

Railway tidak auto-backup di free tier:

**Manual Backup**:
```bash
# Via Railway CLI
railway run mysqldump > backup.sql

# Atau via lokal (get credentials dari Variables):
mysqldump -h railway.host -u user -p database > backup.sql
```

**Recommended**: Setup cron job untuk auto-backup weekly

### 4. Monitoring

**Railway Dashboard Metrics**:
- CPU usage
- Memory usage
- Network traffic
- Response time

**Setup Alerts**:
1. **Settings** → **Observability**
2. Add webhook untuk notifications (Discord/Slack)

### 5. Scaling (Paid Plans)

Jika traffic tinggi:
1. Upgrade ke **Hobby Plan** ($5/month)
2. Adjust **Resources**:
   - Memory: 512MB → 2GB
   - CPU: Shared → Dedicated

---

## 💡 Tips & Best Practices

### 1. Environment Management

Gunakan **Shared Variables** untuk values yang sama across services:
- `APP_ENV=production`
- `LOG_LEVEL=error`

### 2. Database Optimization

Setup index di migration untuk query performance:
```php
$table->index(['nomor_plat']);
$table->index(['created_at']);
```

### 3. Asset Optimization

```bash
# Build assets locally, commit hasil build
npm run build
git add public/build
git commit -m "Add production assets"
```

### 4. Error Tracking

Integrate **Sentry**:
```bash
composer require sentry/sentry-laravel
```

Set `SENTRY_LARAVEL_DSN` di environment variables

### 5. Redis for Caching (Upgrade)

Untuk better performance di paid plan:
1. Add **Redis** service di Railway
2. Update env:
   ```
   CACHE_DRIVER=redis
   REDIS_HOST=${{Redis.REDIS_HOST}}
   REDIS_PORT=${{Redis.REDIS_PORT}}
   ```

---

## 🆚 Railway vs Render

| Feature | Railway | Render |
|---------|---------|--------|
| **Free MySQL** | ✅ Yes (5GB) | ❌ No |
| **Setup Difficulty** | ⭐⭐ Easy | ⭐⭐⭐ Medium |
| **Auto-deploy** | ✅ Yes | ✅ Yes |
| **Logs Quality** | ⭐⭐⭐⭐⭐ Excellent | ⭐⭐⭐ Good |
| **Cold Start** | ✅ None | ⚠️ 15min inactivity |
| **Free Credit** | $5/month | Limited |
| **Shell Access** | ✅ Via CLI | ❌ Paid only |

**Recommendation**: **Railway** untuk Laravel development & small production apps

---

## 📋 Deployment Checklist

- [ ] Code di-push ke GitHub
- [ ] Railway project created
- [ ] MySQL database added
- [ ] Environment variables configured
- [ ] `APP_KEY` generated
- [ ] Domain generated
- [ ] Application accessible via URL
- [ ] Database connection working
- [ ] Migrations ran successfully
- [ ] Admin panel accessible
- [ ] CRUD operations tested
- [ ] Auto-deploy configured

---

## 🔗 Resources

- Railway Docs: https://docs.railway.app
- Railway CLI: https://docs.railway.app/develop/cli
- Railway Discord: https://discord.gg/railway
- Laravel Deployment: https://laravel.com/docs/deployment

---

**Next Step**: Follow langkah-langkah di atas, atau tanya jika ada yang kurang jelas! 🚀
