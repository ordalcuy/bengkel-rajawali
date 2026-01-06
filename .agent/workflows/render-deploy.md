---
description: Deploy aplikasi Laravel ke Render
---

# Deploy Laravel ke Render

## Persiapan Lokal

1. **Pastikan semua perubahan sudah di-commit dan di-push ke GitHub**
```bash
git add .
git commit -m "Prepare for Render deployment"
git push origin main
```

2. **Pastikan Dockerfile sudah ada dan terupdate**
- Cek file `Dockerfile` di root project
- Pastikan menggunakan PHP 8.3 atau versi yang sesuai

## Setup di Render

### 1. Buat Web Service Baru

1. Login ke [render.com](https://render.com)
2. Klik **"+ New"** → **"Web Service"**
3. Connect repository GitHub/GitLab Anda
4. Pilih repository `arifhidayat25/laravel-bengkel`

### 2. Konfigurasi Web Service

Isi form dengan detail berikut:

- **Name**: `laravel-bengkel` (atau nama pilihan Anda)
- **Region**: `Singapore` (terdekat ke Indonesia untuk latency rendah)
- **Branch**: `main`
- **Runtime**: `Docker`
- **Instance Type**: `Free` (atau sesuai kebutuhan)

### 3. Setup Environment Variables

Klik **"Advanced"** dan tambahkan environment variables berikut:

#### App Configuration
```
APP_NAME=Laravel Bengkel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
```

> **Note**: `APP_KEY` akan di-generate setelah deploy pertama kali

#### Database Configuration
```
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

> **Note**: Isi credentials database setelah membuat database di langkah berikutnya

#### Session & Cache
```
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

#### Filament
```
FILAMENT_PATH=admin
```

#### WhatsApp/Fonnte (jika menggunakan)
```
FONNTE_API_TOKEN=your-fonnte-token
```

### 4. Setup Database

Render tidak menyediakan MySQL gratis. Pilih salah satu opsi:

#### Opsi A: PostgreSQL (Gratis dari Render)

1. Buat **PostgreSQL Database** baru di Render:
   - Klik **"+ New"** → **"PostgreSQL"**
   - Pilih region yang sama dengan web service
   - Pilih instance type **"Free"**

2. Setelah database dibuat, copy **Internal Database URL**

3. Update environment variables di Web Service:
   ```
   DB_CONNECTION=pgsql
   DATABASE_URL=<paste-internal-database-url>
   ```

4. Update `config/database.php` jika perlu untuk support PostgreSQL

#### Opsi B: MySQL External (Railway/PlanetScale)

1. Buat database MySQL di:
   - [Railway](https://railway.app) (recommended, ada free tier)
   - [PlanetScale](https://planetscale.com) (free tier tersedia)
   - [FreeSQLDatabase](https://www.freesqldatabase.com) (free MySQL hosting)

2. Dapatkan credentials database (host, port, database name, username, password)

3. Masukkan credentials ke environment variables Render

### 5. Deploy Aplikasi

1. **Klik "Create Web Service"**
2. Tunggu proses build selesai (~5-10 menit)
3. Monitor di tab **"Logs"** untuk melihat progress

### 6. Generate APP_KEY

Setelah deploy pertama kali:

1. Buka **"Shell"** di Render dashboard
2. Jalankan command:
   ```bash
   php artisan key:generate --show
   ```
3. Copy output (format: `base64:...`)
4. Paste ke environment variable `APP_KEY`
5. Klik **"Save Changes"** (aplikasi akan auto-redeploy)

### 7. Jalankan Migration & Seeder

Setelah `APP_KEY` ter-set dan aplikasi redeploy:

1. Buka **"Shell"** di Render dashboard
2. Jalankan migration:
   ```bash
   php artisan migrate --force
   ```

3. (Optional) Jalankan seeder jika diperlukan:
   ```bash
   php artisan db:seed --force
   ```

4. Create storage symlink:
   ```bash
   php artisan storage:link
   ```

5. Clear cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

### 8. Verifikasi & Testing

1. **Akses aplikasi** di URL yang diberikan:
   ```
   https://your-app-name.onrender.com
   ```

2. **Test login** ke Filament admin:
   ```
   https://your-app-name.onrender.com/admin
   ```

3. **Cek apakah semua fitur berjalan**:
   - Login/Register
   - CRUD operations
   - Upload files
   - WhatsApp notifications (jika ada)

## Troubleshooting

### Build Gagal

1. **Cek Logs** di tab "Logs" untuk melihat error
2. **Pastikan dependencies lengkap** di `composer.json`
3. **Cek Dockerfile**:
   - Pastikan PHP version sesuai
   - Pastikan semua extensions terinstall
   - Cek path dan permissions

### Database Connection Error

1. **Verifikasi credentials**:
   ```bash
   # Via Render Shell
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

2. **Cek environment variables** sudah benar
3. **Pastikan database sudah dibuat** dan accessible

### 500 Internal Server Error

1. **Enable debug sementara**:
   - Set `APP_DEBUG=true` (hanya untuk debugging)
   - Lihat error detail
   - **JANGAN lupa set kembali ke `false`**

2. **Clear cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

### Assets/CSS Tidak Muncul

1. **Run build ulang**:
   ```bash
   npm run build
   ```

2. **Clear cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Cek storage link**:
   ```bash
   php artisan storage:link
   ```

### File Upload Tidak Berfungsi

1. **Cek storage permissions**:
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. **Pastikan storage link sudah dibuat**:
   ```bash
   php artisan storage:link
   ```

3. **Untuk Render, pertimbangkan menggunakan cloud storage** (AWS S3, Cloudinary, dll) karena filesystem ephemeral

## Tips & Rekomendasi

### 1. Auto-Deploy

Render akan otomatis redeploy setiap kali ada push ke branch yang di-configure (default: `main`)

### 2. Monitor Aplikasi

- Gunakan tab **"Metrics"** untuk monitor CPU, Memory, dan Bandwidth
- Gunakan tab **"Logs"** untuk debugging

### 3. Custom Domain

1. Buka **"Settings"** → **"Custom Domain"**
2. Tambahkan domain Anda
3. Update DNS records sesuai instruksi Render

### 4. Environment Management

- Gunakan **"Environment Groups"** untuk manage env vars multiple services
- Simpan secret di Render, bukan di git

### 5. File Storage (Penting!)

Render menggunakan **ephemeral filesystem**, artinya:
- Files yang di-upload akan **hilang** saat redeploy
- **Solusi**: Gunakan cloud storage:
  - AWS S3
  - Cloudinary
  - DigitalOcean Spaces
  - Backblaze B2

Konfigurasi di `config/filesystems.php` dan `.env`

### 6. Database Backup

- Setup regular backup untuk production database
- Untuk PostgreSQL Render, gunakan fitur **"Backups"** di dashboard
- Untuk external DB, gunakan backup service mereka

### 7. Monitoring & Logging

- Pertimbangkan menggunakan:
  - [Sentry](https://sentry.io) untuk error tracking
  - [LogRocket](https://logrocket.com) untuk session replay
  - [New Relic](https://newrelic.com) untuk APM

## Maintenance

### Update Aplikasi

1. **Push changes** ke GitHub:
   ```bash
   git add .
   git commit -m "Update feature X"
   git push origin main
   ```

2. Render akan **auto-deploy** (monitor di Logs)

3. **Jalankan migration** jika ada perubahan schema:
   ```bash
   # Via Render Shell
   php artisan migrate --force
   ```

### Rollback

1. Buka **"Deploy"** tab di Render
2. Pilih deploy sebelumnya yang stabil
3. Klik **"Redeploy"**

## Checklist Deploy

- [ ] Code sudah di-push ke GitHub
- [ ] Dockerfile sudah terupdate
- [ ] Environment variables sudah di-set
- [ ] Database sudah dibuat dan accessible
- [ ] APP_KEY sudah di-generate
- [ ] Migration sudah dijalankan
- [ ] Storage link sudah dibuat
- [ ] Cache sudah di-clear
- [ ] Aplikasi bisa diakses dan login berhasil
- [ ] Semua fitur sudah di-test
- [ ] File upload testing (jika ada)
- [ ] WhatsApp notification testing (jika ada)

---

**Selamat! Aplikasi Laravel Anda sudah live di Render! 🎉**
