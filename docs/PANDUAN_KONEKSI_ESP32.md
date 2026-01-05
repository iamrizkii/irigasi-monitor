# 📖 Panduan Lengkap: Menghubungkan ESP32 GESTI ke Website Laravel

Dokumen ini menjelaskan langkah-langkah detail untuk menghubungkan alat ESP32 dengan website monitoring GESTI.

---

## 🔧 Persiapan Awal

### Kebutuhan Hardware
- ESP32 Development Board
- Kabel USB untuk programming
- Sensor kelembaban tanah (4 unit)
- Sensor ultrasonik HC-SR04 (3 unit)
- Servo motor (5 unit)
- Relay module
- LCD I2C 20x4

### Kebutuhan Software
- Arduino IDE (versi 1.8.x atau 2.x)
- Laragon atau XAMPP (untuk menjalankan Laravel)
- Browser (Chrome/Firefox)

---

## 📍 STEP 1: Menjalankan Website Laravel

### 1.1 Buka Command Prompt / PowerShell

Tekan `Windows + R`, ketik `cmd`, lalu Enter.

### 1.2 Masuk ke Folder Project

```bash
cd c:\laragon\www\gesti
```

### 1.3 Jalankan Server Laravel

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

> ⚠️ **PENTING:** 
> - Flag `--host=0.0.0.0` membuat server bisa diakses dari perangkat lain di jaringan yang sama
> - Jangan tutup jendela Command Prompt ini, biarkan tetap berjalan

### 1.4 Verifikasi Server Berjalan

Buka browser dan akses: `http://localhost:8000`

Jika muncul dashboard GESTI, berarti server sudah berjalan dengan benar.

---

## 📍 STEP 2: Mencari IP Address Komputer

ESP32 perlu tahu alamat komputer yang menjalankan website agar bisa mengirim data.

### 2.1 Buka Command Prompt Baru

Tekan `Windows + R`, ketik `cmd`, lalu Enter.

### 2.2 Ketik Perintah ipconfig

```bash
ipconfig
```

### 2.3 Cari IPv4 Address

Lihat bagian **Wireless LAN adapter Wi-Fi** (jika pakai WiFi) atau **Ethernet adapter** (jika pakai kabel):

```
Wireless LAN adapter Wi-Fi:

   Connection-specific DNS Suffix  . :
   IPv4 Address. . . . . . . . . . . : 192.168.1.100  ← INI YANG DICARI
   Subnet Mask . . . . . . . . . . . : 255.255.255.0
   Default Gateway . . . . . . . . . : 192.168.1.1
```

**Catat IP Address ini!** Contoh: `192.168.1.100`

---

## 📍 STEP 3: Setup Arduino IDE

### 3.1 Install Arduino IDE

Jika belum punya, download di: https://www.arduino.cc/en/software

### 3.2 Install ESP32 Board

1. Buka Arduino IDE
2. Pergi ke **File → Preferences**
3. Di kolom **Additional Boards Manager URLs**, tambahkan:
   ```
   https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
   ```
4. Klik **OK**
5. Pergi ke **Tools → Board → Boards Manager**
6. Cari **esp32** dan klik **Install**

### 3.3 Install Library yang Dibutuhkan

Pergi ke **Sketch → Include Library → Manage Libraries**, lalu install:

| Library | Fungsi |
|---------|--------|
| **ArduinoJson** | Parsing JSON untuk komunikasi API |
| **ESP32Servo** | Kontrol servo motor |
| **LiquidCrystal_I2C** | Kontrol LCD |

**Cara install:**
1. Ketik nama library di kolom pencarian
2. Pilih library yang sesuai
3. Klik **Install**

---

## 📍 STEP 4: Edit Kode ESP32

### 4.1 Buka File Kode ESP32

Lokasi file: `c:\laragon\www\gesti\docs\esp32_gesti_wifi.ino`

Buka dengan Arduino IDE.

### 4.2 Edit Konfigurasi WiFi

Cari baris berikut (sekitar baris 33-34):

```cpp
// ================= KONFIGURASI WIFI =================
const char* WIFI_SSID = "NAMA_WIFI_ANDA";
const char* WIFI_PASSWORD = "PASSWORD_WIFI_ANDA";
```

**Ganti dengan:**
```cpp
const char* WIFI_SSID = "NamaWiFiRumahAnda";      // Nama WiFi (case sensitive!)
const char* WIFI_PASSWORD = "PasswordWiFiAnda";   // Password WiFi
```

### 4.3 Edit Konfigurasi Server

Cari baris berikut (sekitar baris 40):

```cpp
// ================= KONFIGURASI SERVER =================
const char* SERVER_URL = "http://192.168.1.100/gesti";
```

**Ganti dengan IP komputer Anda:**
```cpp
const char* SERVER_URL = "http://192.168.1.100:8000";
```

> ⚠️ **Catatan:**
> - Ganti `192.168.1.100` dengan IP komputer Anda dari Step 2
> - Port `8000` harus sama dengan port saat menjalankan `php artisan serve`

---

## 📍 STEP 5: Upload Kode ke ESP32

### 5.1 Hubungkan ESP32 ke Komputer

Gunakan kabel USB untuk menghubungkan ESP32 ke komputer.

### 5.2 Pilih Board

Di Arduino IDE:
1. Pergi ke **Tools → Board → esp32**
2. Pilih **ESP32 Dev Module**

### 5.3 Pilih Port

1. Pergi ke **Tools → Port**
2. Pilih port yang muncul setelah ESP32 terhubung (biasanya COM3, COM4, atau serupa)

> Jika tidak ada port yang muncul:
> - Install driver CP210x atau CH340 (tergantung chip ESP32)
> - Coba kabel USB yang lain

### 5.4 Upload Kode

1. Klik tombol **Upload** (panah ke kanan) atau tekan `Ctrl + U`
2. Tunggu proses compile dan upload selesai
3. Jika ada error, periksa apakah semua library sudah terinstall

---

## 📍 STEP 6: Monitoring Koneksi

### 6.1 Buka Serial Monitor

Di Arduino IDE:
1. Pergi ke **Tools → Serial Monitor**
2. Atau tekan `Ctrl + Shift + M`

### 6.2 Set Baud Rate

Di pojok kanan bawah Serial Monitor, pilih **115200 baud**

### 6.3 Cek Output

**Jika koneksi WiFi berhasil:**
```
Menghubungkan ke WiFi: NamaWiFiRumahAnda
......
WiFi terhubung!
IP Address: 192.168.1.50
```

**Jika pengiriman data berhasil:**
```
===== STATUS SISTEM =====
Mode: auto | WiFi: OK
P1=45%  P2=28%  P3=65%  P4=52%
Air Tengah=3.8  Utama=5.2  Tandon=8.5
Mengirim data ke server...
HTTP Response code: 200
Response: {"success":true,"message":"Data received successfully","id":5}
```

---

## 📍 STEP 7: Verifikasi di Dashboard

### 7.1 Buka Dashboard

Di browser, akses: `http://192.168.1.100:8000`

(Ganti dengan IP komputer Anda)

### 7.2 Cek Data Real-time

- Nilai kelembaban tanah di 4 card Petak harus berubah sesuai pembacaan sensor
- Status pompa dan gerbang harus sesuai dengan kondisi alat
- Grafik akan mulai terisi dengan data

### 7.3 Test Mode Manual

1. Klik tombol **Manual** di dashboard
2. Coba klik tombol **Pompa ON**
3. Cek di alat apakah pompa menyala
4. Cek di Serial Monitor apakah mode berubah menjadi "manual"

---

## ⚠️ Troubleshooting

### WiFi Tidak Connect

| Kemungkinan Masalah | Solusi |
|---------------------|--------|
| SSID salah | Pastikan nama WiFi persis sama (case sensitive) |
| Password salah | Cek ulang password WiFi |
| WiFi 5GHz | ESP32 hanya support WiFi 2.4GHz |
| Jarak terlalu jauh | Dekatkan ESP32 ke router |

### HTTP Error -1

| Kemungkinan Masalah | Solusi |
|---------------------|--------|
| IP Address salah | Cek ulang IP komputer dengan `ipconfig` |
| Server tidak jalan | Pastikan `php artisan serve` masih berjalan |
| Firewall memblokir | Matikan Windows Firewall sementara atau tambahkan exception |
| Beda jaringan | Pastikan komputer dan ESP32 di WiFi yang sama |

### Data Tidak Muncul di Dashboard

| Kemungkinan Masalah | Solusi |
|---------------------|--------|
| Database belum migrate | Jalankan `php artisan migrate` |
| Error di controller | Cek file log di `storage/logs/laravel.log` |
| Browser cache | Refresh dengan `Ctrl + F5` |

---

## 📊 Diagram Alur Komunikasi

```
┌─────────────┐                      ┌─────────────────┐
│   ESP32     │                      │  Website Laravel │
│             │                      │                  │
│  Baca       │   POST /api/         │  Simpan ke      │
│  Sensor  ───┼──► sensor-data ────► │  Database       │
│             │                      │                  │
│  Jalankan   │   GET /api/          │  Kirim          │
│  Perintah ◄─┼── device-status ◄─── │  Perintah       │
│             │                      │                  │
└─────────────┘                      └─────────────────┘
      │                                      │
      │         WiFi Network                 │
      └──────────────────────────────────────┘
```

---

## ✅ Checklist Sebelum Menjalankan

- [ ] Website Laravel sudah berjalan (`php artisan serve --host=0.0.0.0`)
- [ ] IP komputer sudah dicatat
- [ ] WiFi SSID dan password sudah benar di kode ESP32
- [ ] SERVER_URL sudah diisi dengan IP komputer
- [ ] Library ArduinoJson sudah terinstall
- [ ] ESP32 sudah terhubung ke komputer
- [ ] Board dan Port sudah dipilih dengan benar
- [ ] Kode sudah di-upload ke ESP32
- [ ] Serial Monitor menunjukkan "WiFi terhubung"
- [ ] Serial Monitor menunjukkan "HTTP Response code: 200"
- [ ] Dashboard menampilkan data dari ESP32
