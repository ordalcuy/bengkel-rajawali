
-----

# Sistem Antrean Bengkel Rajawali Motor

Sistem Antrean Bengkel Rajawali Motor adalah aplikasi web modern yang dirancang untuk mendigitalisasi dan mengoptimalkan seluruh alur kerja operasional bengkel. Dibangun dengan tumpukan teknologi canggih, sistem ini menghadirkan fitur-fitur real-time untuk meningkatkan efisiensi, transparansi, dan pengalaman pelanggan.

## ✨ Fitur Utama

Sistem ini dirancang dengan berbagai fitur canggih untuk memenuhi kebutuhan bengkel modern:

  * [cite\_start]**Manajemen Antrean Dinamis**: Alur kerja lengkap mulai dari pendaftaran antrean, penugasan mekanik, hingga penyelesaian pekerjaan, semuanya dikelola melalui antarmuka yang intuitif. [cite: 7]
  * [cite\_start]**Penomoran Antrean Cerdas**: Nomor antrean digenerate secara otomatis dengan prefix berdasarkan jenis layanan (misal: **A001** untuk Ringan, **B001** untuk Sedang) dan direset setiap hari. [cite: 18]
  * [cite\_start]**Hak Akses Berbasis Peran**: Dua peran utama dengan hak akses yang terdefinisi dengan jelas (Kasir dan Owner) menggunakan **Spatie Laravel Permission**. [cite: 3]
  * [cite\_start]**Dashboard Laporan Interaktif**: Halaman khusus untuk Owner yang menampilkan statistik performa harian (total antrean, pendapatan, dll) dan tabel laporan detail yang bisa difilter berdasarkan tanggal. [cite: 9, 21]
  * **Tampilan Publik Real-Time**:
      * **Layar Panggilan (`/display`)**: Menampilkan nomor antrean yang sedang dilayani secara besar, lengkap dengan detail layanan, plat nomor, nama mekanik, dan slot video promosi.
      * **Monitor Pengerjaan (`/waiting-list`)**: Menampilkan semua pekerjaan yang sedang aktif, dikelompokkan berdasarkan jenis layanan, memberikan visibilitas penuh bagi pelanggan di ruang tunggu.
  * **Notifikasi Suara (Text-to-Speech)**:
      * Suara panggilan otomatis di Layar Panggilan saat mekanik ditugaskan.
      * Notifikasi suara di panel admin saat pekerjaan diselesaikan.

## 🚀 Teknologi yang Digunakan

  * **Backend**: Laravel
  * **Admin Panel**: Filament
  * **Real-time & WebSockets**: Laravel Reverb
  * **Manajemen Peran**: Spatie Laravel Permission
  * **Frontend**: Tailwind CSS, Blade, Alpine.js (via Filament)
  * **Database**: MySQL / MariaDB

## 📸 Tampilan Sistem

\<table\>
\<tr\>
\<td align="center"\>\<b\>Panel Admin\</b\>\</td\>
\<td align="center"\>\<b\>Dashboard Laporan (Owner)\</b\>\</td\>
\</tr\>
\<tr\>
\<td\>\<img src="[https://i.imgur.com/uQW5gJ0.png](https://www.google.com/search?q=https://i.imgur.com/uQW5gJ0.png)" alt="Panel Admin"\>\</td\>
\<td\>\<img src="[https://i.imgur.com/2Yh8V1F.png](https://www.google.com/search?q=https://i.imgur.com/2Yh8V1F.png)" alt="Dashboard Laporan"\>\</td\>
\</tr\>
\<tr\>
\<td align="center"\>\<b\>Layar Panggilan Detail\</b\>\</td\>
\<td align="center"\>\<b\>Monitor Pengerjaan Aktif\</b\>\</td\>
\</tr\>
\<tr\>
\<td\>\<img src="[https://i.imgur.com/f0yN8gH.png](https://www.google.com/search?q=https://i.imgur.com/f0yN8gH.png)" alt="Layar Panggilan"\>\</td\>
\<td\>\<img src="[https://i.imgur.com/tCjW5oA.png](https://www.google.com/search?q=https://i.imgur.com/tCjW5oA.png)" alt="Monitor Pengerjaan"\>\</td\>
\</tr\>
\</table\>

## 🛠️ Panduan Instalasi dan Setup

Berikut adalah langkah-langkah untuk menjalankan proyek ini di lingkungan pengembangan lokal.

### 1\. Prasyarat

  - PHP (versi 8.1+)
  - Composer
  - Node.js & NPM
  - Database (MySQL/MariaDB)

### 2\. Instalasi

1.  **Clone repositori ini:**

    ```bash
    git clone https://github.com/username/repo-name.git
    cd repo-name
    ```

2.  **Install dependensi PHP dan JavaScript:**

    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Lingkungan:**

      - Salin file `.env.example` menjadi `.env`.
        ```bash
        copy .env.example .env
        ```
      - Buat kunci aplikasi baru.
        ```bash
        php artisan key:generate
        ```
      - Atur koneksi database Anda di dalam file `.env`.
        ```env
        DB_DATABASE=db_bengkel
        DB_USERNAME=root
        DB_PASSWORD=
        ```

4.  **Jalankan Migrasi Database:**
    Perintah ini akan membuat semua tabel yang dibutuhkan oleh aplikasi.

    ```bash
    php artisan migrate
    ```

5.  **Setup Awal (Opsional):** Jika Anda memiliki Seeder untuk membuat data awal (user, layanan, dll), jalankan:

    ```bash
    php artisan db:seed
    ```

    Jika tidak, buat user pertama Anda secara manual melalui `php artisan tinker` atau fitur registrasi.

### 3\. Menjalankan Aplikasi

Anda perlu menjalankan **tiga proses** ini secara bersamaan di terminal yang berbeda.

  - **Terminal 1: Jalankan Server Aplikasi**

    ```bash
    php artisan serve
    ```

  - **Terminal 2: Jalankan Server Real-time Reverb**

    ```bash
    php artisan reverb:start
    ```

  - **Terminal 3: Jalankan Vite untuk Aset Frontend**

    ```bash
    npm run dev
    ```

Setelah semua berjalan, aplikasi Anda akan dapat diakses di `http://127.0.0.1:8000`.

  - **Panel Admin**: `http://127.0.0.1:8000/admin`
  - **Layar Panggilan**: `http://127.0.0.1:8000/display`
  - **Monitor Pengerjaan**: `http://127.0.0.1:8000/waiting-list`

-----

## 📄 Lisensi

Proyek ini dilisensikan di bawah [Lisensi MIT](LICENSE.md).