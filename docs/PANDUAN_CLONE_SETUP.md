# 📖 Panduan Clone & Setup Project GESTI

Panduan lengkap untuk mengunduh dan menjalankan website GESTI - Sistem Monitoring Irigasi IoT.

---

## 📋 Persiapan

### Software yang Dibutuhkan

| Software | Versi | Download |
|----------|-------|----------|
| **XAMPP/Laragon** | Terbaru | [laragon.org](https://laragon.org/download/) |
| **Composer** | 2.x | [getcomposer.org](https://getcomposer.org/download/) |
| **Git** | Terbaru | [git-scm.com](https://git-scm.com/downloads) |
| **Code Editor** | VS Code | [code.visualstudio.com](https://code.visualstudio.com/) |

### Pastikan Sudah Terinstall

Buka Command Prompt dan ketik:
```bash
php -v          # Harus muncul PHP 8.x
composer -V     # Harus muncul Composer 2.x
git --version   # Harus muncul git version x.x
```

---

## 🚀 STEP 1: Clone Repository

### 1.1 Buka Command Prompt

Tekan `Windows + R`, ketik `cmd`, lalu Enter.

### 1.2 Masuk ke Folder Web Server

**Jika pakai Laragon:**
```bash
cd C:\laragon\www
```

**Jika pakai XAMPP:**
```bash
cd C:\xampp\htdocs
```

### 1.3 Clone Repository

```bash
git clone https://github.com/iamrizkii/irigasi-monitor.git
```

Tunggu sampai proses selesai:
```
Cloning into 'irigasi-monitor'...
remote: Enumerating objects: 94, done.
remote: Counting objects: 100% (94/94), done.
...
Receiving objects: 100% (94/94), done.
```

### 1.4 Masuk ke Folder Project

```bash
cd irigasi-monitor
```

---

## 🚀 STEP 2: Install Dependencies

### 2.1 Install PHP Dependencies

```bash
composer install
```

Tunggu sampai selesai (bisa 2-5 menit):
```
Installing dependencies from lock file
...
Package manifest generated successfully.
```

> ⚠️ Jika ada error, pastikan PHP dan Composer sudah terinstall dengan benar.

---

## 🚀 STEP 3: Konfigurasi Environment

### 3.1 Copy File Environment

```bash
copy .env.example .env
```

### 3.2 Generate Application Key

```bash
php artisan key:generate
```

Output:
```
INFO  Application key set successfully.
```

### 3.3 Edit File .env

Buka file `.env` dengan Notepad atau VS Code:
```bash
notepad .env
```

Cari dan ubah bagian database:

**Sebelum:**
```
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

**Sesudah:**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gesti
DB_USERNAME=root
DB_PASSWORD=
```

> 📝 Jika MySQL Anda punya password, isi di `DB_PASSWORD=`

Simpan dan tutup file.

---

## 🚀 STEP 4: Buat Database

### 4.1 Buka phpMyAdmin

1. Pastikan Laragon/XAMPP sudah running
2. Buka browser: `http://localhost/phpmyadmin`

### 4.2 Buat Database Baru

1. Klik **New** di sidebar kiri
2. Isi nama database: `gesti`
3. Pilih collation: `utf8mb4_unicode_ci`
4. Klik **Create**

**Atau via Command Line:**
```bash
mysql -u root -e "CREATE DATABASE gesti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 🚀 STEP 5: Jalankan Migrasi Database

```bash
php artisan migrate
```

Output yang benar:
```
INFO  Running migrations.

0001_01_01_000000_create_users_table .......... DONE
0001_01_01_000001_create_cache_table .......... DONE
0001_01_01_000002_create_jobs_table ........... DONE
2024_01_05_000001_create_sensor_readings_table  DONE
2024_01_05_000002_create_device_settings_table  DONE
2024_01_05_000003_create_alerts_table ......... DONE
```

---

## 🚀 STEP 6: Jalankan Website

### 6.1 Start Server

```bash
php artisan serve
```

Output:
```
INFO  Server running on [http://127.0.0.1:8000].
Press Ctrl+C to stop the server
```

### 6.2 Buka Browser

Akses: **http://localhost:8000**

🎉 **Website GESTI sudah berjalan!**

---

## 📱 STEP 7: Setup ESP32 (Opsional)

Jika ingin menghubungkan dengan alat ESP32:

### 7.1 Jalankan Server dengan Host Terbuka

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### 7.2 Cari IP Komputer

```bash
ipconfig
```

Catat IPv4 Address (contoh: `192.168.1.100`)

### 7.3 Edit Kode ESP32

Buka file `docs/esp32_gesti_wifi.ino` dan edit:

```cpp
const char* WIFI_SSID = "NAMA_WIFI_ANDA";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";
const char* SERVER_URL = "http://192.168.1.100:8000";
```

### 7.4 Upload ke ESP32

Upload kode via Arduino IDE.

---

## ✅ Ringkasan Perintah

```bash
# Clone
git clone https://github.com/iamrizkii/irigasi-monitor.git
cd irigasi-monitor

# Install
composer install

# Konfigurasi
copy .env.example .env
php artisan key:generate 

# Edit .env (sesuaikan database)
notepad .env

# Migrasi database
php artisan migrate

# Jalankan
php artisan serve
```

---

## ⚠️ Troubleshooting

| Error | Solusi |
|-------|--------|
| `composer: command not found` | Install Composer dari getcomposer.org |
| `SQLSTATE[HY000] [1049] Unknown database` | Buat database `gesti` di phpMyAdmin |
| `SQLSTATE[HY000] [2002] Connection refused` | Pastikan MySQL/MariaDB sudah running |
| `Port 8000 already in use` | Gunakan port lain: `php artisan serve --port=8001` |
| `Class not found` | Jalankan `composer dump-autoload` |

---

## 📁 Struktur Folder Penting

```
irigasi-monitor/
├── app/
│   ├── Http/Controllers/
│   │   ├── ApiController.php      ← API untuk ESP32
│   │   └── DashboardController.php
│   └── Models/
│       ├── SensorReading.php
│       ├── DeviceSetting.php
│       └── Alert.php
├── database/migrations/           ← Struktur tabel
├── resources/views/
│   ├── dashboard.blade.php        ← Halaman utama
│   └── history.blade.php          ← Halaman riwayat
├── routes/
│   ├── api.php                    ← API routes
│   └── web.php                    ← Web routes
├── docs/
│   ├── esp32_gesti_wifi.ino       ← Kode ESP32
│   └── PANDUAN_KONEKSI_ESP32.md   ← Panduan ESP32
└── .env                           ← Konfigurasi
```

---

## 🔗 Links

- **Repository:** https://github.com/iamrizkii/irigasi-monitor
- **Laravel Docs:** https://laravel.com/docs
- **Composer:** https://getcomposer.org
